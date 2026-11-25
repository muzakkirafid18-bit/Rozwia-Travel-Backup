<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\StoreUserRequest;
use App\Http\Requests\Administrator\UpdateUserRequest;
use App\Mail\AccountCredentialsMail;
use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\JamaahDetail;
use App\Models\User;
use App\Services\AsyncMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $role   = (string) $request->input('role', 'semua');
        $status = (string) $request->input('status', 'semua');

        $query = User::query()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        if ($role !== '' && $role !== 'semua' && in_array($role, User::ROLES, true)) {
            $query->where('role', $role);
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status_akun', $status);
        }

        $users = $query->paginate(15)->withQueryString();

        // load log email terakhir biar nggak kena N+1 query
        $emailLogsByUser = EmailLog::whereIn('id_pengguna', $users->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->unique('id_pengguna')
            ->keyBy('id_pengguna');

        $users->getCollection()->transform(function (User $u) use ($emailLogsByUser) {
            $u->setAttribute('latest_email_log', $emailLogsByUser->get($u->id));
            return $u;
        });

        $roleCounts = User::select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $stats = [
            'total'         => (int) $roleCounts->sum(),
            'administrator' => (int) ($roleCounts['administrator'] ?? 0),
            'supervisor'    => (int) ($roleCounts['supervisor'] ?? 0),
            'admin'         => (int) ($roleCounts['admin'] ?? 0),
            'jamaah'        => (int) ($roleCounts['jamaah'] ?? 0),
            'active'        => (int) User::where('status_akun', 'active')->count(),
            'inactive'      => (int) User::where('status_akun', '!=', 'active')->count(),
        ];

        $activeAdminCount = User::where('role', 'administrator')
            ->where('status_akun', 'active')
            ->count();

        return view('administrator.dashboard', compact(
            'users',
            'stats',
            'search',
            'role',
            'status',
            'activeAdminCount'
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated    = $request->validated();
        $tempPassword = $this->generateStrongPassword();

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'nomor_telepon'        => $validated['nomor_telepon'],
            'role'                 => $validated['role'],
            'status_akun'          => $validated['status_akun'],
            'password'             => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        $reason = 'Pembuatan Akun ' . $user->roleLabel();
        $this->dispatchCredentialsEmail($user, $tempPassword, $reason);
        $this->auditAction($request, 'user.created', $user, [
            'role'   => $user->role,
            'status' => $user->status_akun,
        ]);

        return back()->with(
            'success',
            "Akun {$user->roleLabel()} untuk {$user->name} ({$user->email}) berhasil dibuat. Password sementara yang aman telah dikirimkan ke email pengguna."
        );
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $target    = User::findOrFail($id);
        $validated = $request->validated();
        $actor     = $request->user();

        if ($target->id === $actor->id) {
            if ($validated['role'] !== $target->role) {
                return back()->with('error', 'Demi keamanan sistem, Anda tidak dapat mengubah role akun Anda sendiri.');
            }
            if ($validated['status_akun'] !== 'active') {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }

        if ($this->wouldLeaveWithoutActiveAdministrator($target, $validated['role'], $validated['status_akun'])) {
            return back()->with('error', 'Sistem wajib memiliki minimal 1 akun Administrator aktif.');
        }

        $oldRole   = $target->role;
        $oldStatus = $target->status_akun;

        $target->update([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'nomor_telepon'  => $validated['nomor_telepon'],
            'role'           => $validated['role'],
            'status_akun'    => $validated['status_akun'],
            'remember_token' => $validated['status_akun'] !== 'active' ? null : $target->remember_token,
        ]);

        $this->auditAction($request, 'user.updated', $target, [
            'old_role'   => $oldRole,
            'new_role'   => $target->role,
            'old_status' => $oldStatus,
            'new_status' => $target->status_akun,
        ]);

        return back()->with('success', "Data akun {$target->name} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $target = User::findOrFail($id);

        if ($target->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $newStatus = $target->status_akun === 'active' ? 'inactive' : 'active';

        if ($this->wouldLeaveWithoutActiveAdministrator($target, $target->role, $newStatus)) {
            return back()->with('error', 'Tidak dapat menonaktifkan satu-satunya Administrator yang aktif.');
        }

        $target->update([
            'status_akun'    => $newStatus,
            'remember_token' => $newStatus !== 'active' ? null : $target->remember_token,
        ]);

        $this->auditAction($request, 'user.status_toggled', $target, [
            'new_status' => $newStatus,
        ]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$target->name} ({$target->roleLabel()}) berhasil {$label}.");
    }

    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $target       = User::findOrFail($id);
        $tempPassword = $this->generateStrongPassword();

        $target->forceFill([
            'password'             => Hash::make($tempPassword),
            'must_change_password' => true,
            'password_changed_at'  => null,
            'remember_token'       => null,
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $target->id)->delete();
        }

        $reason = 'Reset Password Akun ' . $target->roleLabel();
        $this->dispatchCredentialsEmail($target, $tempPassword, $reason);
        $this->auditAction($request, 'user.password_reset', $target);

        return back()->with(
            'success',
            "Password akun {$target->name} ({$target->email}) berhasil di-reset. Password sementara baru: {$tempPassword} (Kredensial juga telah dikirim ke email)."
        );
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $target = User::findOrFail($id);

        if ($target->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($target->role === 'administrator' && $target->status_akun === 'active'
            && User::where('role', 'administrator')->where('status_akun', 'active')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya Administrator aktif di sistem.');
        }

        $targetEmail = $target->email;
        $targetName  = $target->name;
        $targetRole  = $target->role;

        $privateFiles = $this->collectUserPrivateFiles($target);

        DB::transaction(function () use ($target, $privateFiles) {
            Booking::where('id_pengguna', $target->id)->delete();
            $target->delete();

            if (!empty($privateFiles)) {
                DB::afterCommit(fn () => Storage::disk('private')->delete($privateFiles));
            }
        });

        Log::info('[ADMINISTRATOR_AUDIT]', [
            'actor_id'     => $request->user()->id,
            'actor_email'  => $request->user()->email,
            'action'       => 'user.deleted',
            'target_email' => $targetEmail,
            'target_role'  => $targetRole,
            'ip'           => $request->ip(),
        ]);

        return back()->with('success', "Akun {$targetName} ({$targetEmail}) beserta seluruh data terkait berhasil dihapus permanen.");
    }

    public function roleMatrix(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $query = User::query()->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25)->withQueryString();
        $activeAdminCount = User::where('role', 'administrator')->where('status_akun', 'active')->count();

        return view('administrator.role-matrix', compact('users', 'search', 'activeAdminCount'));
    }

    public function updateRoleMatrix(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'roles'   => ['required', 'array'],
            'roles.*' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        $actor = $request->user();
        $rolesInput = $validated['roles'];

        if (isset($rolesInput[$actor->id]) && $rolesInput[$actor->id] !== 'administrator') {
            return back()->with('error', 'Demi keamanan sistem, Anda tidak dapat menurunkan role akun Anda sendiri.');
        }

        $allActiveAdmins = User::where('role', 'administrator')
            ->where('status_akun', 'active')
            ->pluck('id');

        $targets = User::whereIn('id', array_keys($rolesInput))->get()->keyBy('id');

        // hitung sisa super admin aktif setelah update
        $remainingActiveAdmins = $allActiveAdmins->filter(function (int $adminId) use ($rolesInput) {
            return ($rolesInput[$adminId] ?? 'administrator') === 'administrator';
        })->count();

        $promotedToAdmin = $targets->filter(function (User $u) use ($rolesInput) {
            return $u->role !== 'administrator'
                && $u->status_akun === 'active'
                && ($rolesInput[$u->id] ?? $u->role) === 'administrator';
        })->count();

        if (($remainingActiveAdmins + $promotedToAdmin) < 1) {
            return back()->with('error', 'Sistem wajib memiliki minimal 1 akun Administrator aktif.');
        }

        $updatedCount = 0;

        DB::transaction(function () use ($targets, $rolesInput, $request, &$updatedCount) {
            foreach ($targets as $userId => $user) {
                $newRole = $rolesInput[$userId];
                if ($user->role === $newRole) {
                    continue;
                }

                $oldRole = $user->role;
                $user->update(['role' => $newRole]);
                $updatedCount++;

                $this->auditAction($request, 'user.role_matrix_updated', $user, [
                    'old_role' => $oldRole,
                    'new_role' => $newRole,
                ]);
            }
        });

        if ($updatedCount === 0) {
            return back()->with('success', 'Tidak ada perubahan role yang perlu disimpan.');
        }

        return back()->with('success', "Berhasil memperbarui role untuk {$updatedCount} akun pengguna melalui matriks ceklis.");
    }

    private function wouldLeaveWithoutActiveAdministrator(User $target, string $newRole, string $newStatus): bool
    {
        $wasActiveAdmin = $target->role === 'administrator' && $target->status_akun === 'active';
        $willRemainActiveAdmin = $newRole === 'administrator' && $newStatus === 'active';

        if (!$wasActiveAdmin || $willRemainActiveAdmin) {
            return false;
        }

        return User::where('role', 'administrator')
            ->where('status_akun', 'active')
            ->count() <= 1;
    }

    /**
     * Kumpulkan seluruh file private milik user (berkas dokumen & bukti transfer)
     * agar ikut bersih saat akun dihapus permanen.
     *
     * @return array<int, string>
     */
    private function collectUserPrivateFiles(User $user): array
    {
        $bookings = Booking::with([
            'jamaahDetails.dokumens',
            'invoices.riwayatPembayarans',
        ])->where('id_pengguna', $user->id)->get();

        return $bookings
            ->flatMap(fn (Booking $booking) => $booking->jamaahDetails)
            ->flatMap(fn (JamaahDetail $jamaah) => $jamaah->dokumens)
            ->pluck('file_path')
            ->merge(
                $bookings
                    ->flatMap(fn (Booking $booking) => $booking->invoices)
                    ->flatMap(fn (Invoice $invoice) => $invoice->riwayatPembayarans)
                    ->pluck('bukti_transfer')
            )
            ->filter(fn ($path) => filled($path) && $path !== 'via-whatsapp')
            ->unique()
            ->values()
            ->all();
    }

    private function generateStrongPassword(): string
    {
        return Str::password(14, letters: true, numbers: true, symbols: true, spaces: false);
    }

    private function dispatchCredentialsEmail(User $user, string $plainPassword, string $reason): void
    {
        $token = Str::random(48);
        EmailLog::create([
            'id_pengguna' => $user->id,
            'email'       => $user->email,
            'type'        => 'credentials',
            'subject'     => "{$reason} | ROZWIA Islamic Travel",
            'token'       => $token,
        ]);

        AsyncMailer::sendMailable($user->email, new AccountCredentialsMail($user, $plainPassword, $reason, $token));
    }

    private function auditAction(Request $request, string $action, User $target, array $extra = []): void
    {
        Log::info('[ADMINISTRATOR_AUDIT]', array_merge([
            'actor_id'    => $request->user()?->id,
            'actor_email' => $request->user()?->email,
            'action'      => $action,
            'target_id'   => $target->id,
            'target_email'=> $target->email,
            'ip'          => $request->ip(),
        ], $extra));
    }
}
