<?php

namespace App\Support;

class AdminAccess
{
    public const VIEW_DASHBOARD = 'view-admin-dashboard';
    public const MANAGE_USERS = 'manage-users';
    public const MANAGE_PRODUCTS = 'manage-products';
    public const MANAGE_EMAIL_TEMPLATES = 'manage-email-templates';
    public const MANAGE_QUOTATIONS = 'manage-quotations';
    public const MANAGE_MAIL = 'manage-mail';
    public const MANAGE_ACCESS_CONTROL = 'manage-roles-permissions';

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            self::VIEW_DASHBOARD,
            self::MANAGE_USERS,
            self::MANAGE_PRODUCTS,
            self::MANAGE_EMAIL_TEMPLATES,
            self::MANAGE_QUOTATIONS,
            self::MANAGE_MAIL,
            self::MANAGE_ACCESS_CONTROL,
        ];
    }

    /**
     * @return array<int, array{label: string, permission: string}>
     */
    public static function modules(): array
    {
        return [
            ['label' => 'Dashboard', 'permission' => self::VIEW_DASHBOARD],
            ['label' => 'Users', 'permission' => self::MANAGE_USERS],
            ['label' => 'Products', 'permission' => self::MANAGE_PRODUCTS],
            ['label' => 'Email Templates', 'permission' => self::MANAGE_EMAIL_TEMPLATES],
            ['label' => 'Quotations', 'permission' => self::MANAGE_QUOTATIONS],
            ['label' => 'Mail', 'permission' => self::MANAGE_MAIL],
            ['label' => 'Roles & Permissions', 'permission' => self::MANAGE_ACCESS_CONTROL],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function defaultStaffPermissions(): array
    {
        return [
            self::VIEW_DASHBOARD,
            self::MANAGE_QUOTATIONS,
            self::MANAGE_MAIL,
        ];
    }

    public static function label(string $permission): string
    {
        return match ($permission) {
            self::VIEW_DASHBOARD => 'View Dashboard',
            self::MANAGE_USERS => 'Manage Users',
            self::MANAGE_PRODUCTS => 'Manage Products',
            self::MANAGE_EMAIL_TEMPLATES => 'Manage Email Templates',
            self::MANAGE_QUOTATIONS => 'Manage Quotations',
            self::MANAGE_MAIL => 'Manage Mail',
            self::MANAGE_ACCESS_CONTROL => 'Manage Roles & Permissions',
            default => ucwords(str_replace(['-', '_'], ' ', $permission)),
        };
    }
}
