<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

use Domain\Enum\EnumInterface;

/**
 * Палитра цветов для статусов жизненного цикла.
 *
 * Цвета статусов жизненного цикла настраиваются пользователем, но при этом
 * должны быть ограничены определённым набором, чтобы сохранять читабельность
 * и единообразие интерфейса.
 */
enum LifecycleColorPalette: string implements EnumInterface
{
    case DEFAULT   = 'default';   // grey, "no opinion"
    case PRIMARY   = 'primary';   // blue, "fresh / new"
    case SECONDARY = 'secondary'; // muted grey, "archived / cancelled"
    case SUCCESS   = 'success';   // green, "completed successfully"
    case INFO      = 'info';      // cyan, "in transit / informational"
    case WARNING   = 'warning';   // amber, "needs attention / pending approval"
    case DANGER    = 'danger';    // red, "rejected / failed"
    case LIGHT     = 'light';     // near-white, "draft"
    case DARK      = 'dark';      // near-black, "final / locked"
    
    /** UI label suffix (matches `.ui-label-{token}`). */
    public function cssToken(): string
    {
        return $this->value;
    }
    
    /** Russian human-readable label for admin UI. */
    public function name(): string
    {
        return match ($this) {
            self::DEFAULT   => 'По умолчанию',
            self::PRIMARY   => 'Основной (синий)',
            self::SECONDARY => 'Вторичный (серый)',
            self::SUCCESS   => 'Успех (зелёный)',
            self::INFO      => 'Информация (голубой)',
            self::WARNING   => 'Предупреждение (жёлтый)',
            self::DANGER    => 'Опасность (красный)',
            self::LIGHT     => 'Светлый',
            self::DARK      => 'Тёмный',
        };
    }
    
    /** Semantic description so consumers pick the right token. */
    public function description(): string
    {
        return match ($this) {
            self::DEFAULT   => 'Нейтральное состояние без эмоциональной нагрузки',
            self::PRIMARY   => 'Начальное / открытое состояние, требующее внимания',
            self::SECONDARY => 'Архивное / отменённое состояние',
            self::SUCCESS   => 'Успешное завершение жизненного цикла',
            self::INFO      => 'Промежуточное информационное состояние (в работе)',
            self::WARNING   => 'Требует решения (например, на согласовании)',
            self::DANGER    => 'Отклонено, провалено, ошибочное состояние',
            self::LIGHT     => 'Черновик, неактивное состояние',
            self::DARK      => 'Заблокировано, финальное необратимое состояние',
        };
    }
}