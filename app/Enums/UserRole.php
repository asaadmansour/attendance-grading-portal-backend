<?php

namespace App\Enums;

enum UserRole: string
{
    case BranchManager = 'branch_manager';
    case TrackAdmin = 'track_admin';
    case Instructor = 'instructor';
    case Student = 'student';

    public function canCreate(): array
    {
        return match ($this) {
            // the BM provisions track admins, and instructors just like a track admin
            self::BranchManager => [self::TrackAdmin, self::Instructor],
            self::TrackAdmin => [self::Instructor, self::Student],
            default => [],
        };
    }
}
