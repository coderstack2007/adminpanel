<?php
// ═══════════════════════════════════════════════════════════════
// app/Services/NotificationService.php
// Адаптирован под твои роли: hr_manager, super_admin
// ═══════════════════════════════════════════════════════════════

namespace App\Services;

use App\Models\Notification;
use App\Models\VacancyRequest;
use App\Models\User;

class NotificationService
{
    public static function send(User|int $user, VacancyRequest $request, string $type, string $message): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Notification::create([
            'user_id'            => $userId,
            'vacancy_request_id' => $request->id,
            'type'               => $type,
            'message'            => $message,
        ]);
    }

    /**
     * DepartmentHead отправил заявку → уведомить всех hr_manager
     */
    public static function onSubmittedToHr(VacancyRequest $request): void
    {
        // Используй свою систему ролей:
        // Если Spatie: User::role('hr_manager')->get()
        // Если своя:   User::where('role', 'hr_manager')->get()
        $hrUsers = User::role('hr_manager')->get();
        $positionName = $request->position?->name ?? 'должность';

        foreach ($hrUsers as $hr) {
            static::send($hr, $request, 'submitted',
                "📋 Новая заявка: «{$positionName}» от {$request->requester?->name}"
            );
        }
    }

    /**
     * HR отредактировал заявку → уведомить заявителя
     */
    public static function onHrEdited(VacancyRequest $request, User $editor): void
    {
        static::send($request->requester_id, $request, 'hr_edited',
            "✏️ HR ({$editor->name}) внёс изменения в вашу заявку на «{$request->position?->name}»"
        );
    }

    /**
     * HR отправил заявку super_admin'у → уведомить всех super_admin
     */
    public static function onSentToSupervisor(VacancyRequest $request): void
    {
        // Уведомляем всех super_admin (не одного конкретного)
        $supervisors = User::role('super_admin')->get();
        $positionName = $request->position?->name ?? 'должность';

        foreach ($supervisors as $supervisor) {
            static::send($supervisor, $request, 'supervisor_review',
                "📝 Заявка на подбор «{$positionName}» ожидает вашего решения"
            );
        }
    }

    /**
     * Supervisor принял решение → уведомить hr_manager и заявителя
     */
    public static function onSupervisorDecision(VacancyRequest $request, string $decision): void
    {
        $labels = [
            'approved' => 'одобрена ✅',
            'rejected' => 'отклонена ❌',
            'on_hold'  => 'приостановлена ⏸',
        ];
        $label        = $labels[$decision] ?? $decision;
        $positionName = $request->position?->name ?? 'должность';
        $supervisorName = auth()->user()?->name ?? 'Руководитель';

        // → Заявителю
        static::send($request->requester_id, $request, "supervisor_{$decision}",
            "Ваша заявка на «{$positionName}» {$label} руководителем {$supervisorName}"
        );

        // → HR (кто редактировал, иначе всем hr_manager)
        if ($request->hr_editor_id) {
            static::send($request->hr_editor_id, $request, "supervisor_{$decision}",
                "Заявка на «{$positionName}» от {$request->requester?->name} {$label}"
            );
        } else {
            $hrUsers = User::role('hr_manager')->get();
            foreach ($hrUsers as $hr) {
                static::send($hr, $request, "supervisor_{$decision}",
                    "Заявка на «{$positionName}» от {$request->requester?->name} {$label}"
                );
            }
        }
    }

    /**
     * HR закрыл вакансию → уведомить заявителя для подтверждения
     */
    public static function onClosedByHr(VacancyRequest $request): void
    {
        static::send($request->requester_id, $request, 'closed',
            "🔒 Вакансия «{$request->position?->name}» закрыта. Пожалуйста, подтвердите закрытие."
        );
    }

    /**
     * Заявитель подтвердил закрытие → уведомить hr_manager
     */
    public static function onConfirmedClosed(VacancyRequest $request): void
    {
        if ($request->hr_editor_id) {
            static::send($request->hr_editor_id, $request, 'confirmed_closed',
                "✅ Заявитель подтвердил закрытие вакансии «{$request->position?->name}»"
            );
        }
    }
}