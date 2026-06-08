<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;

class AnnouncementController extends Controller
{
    public function store(StoreAnnouncementRequest $request)
    {
        $announcement = $request->user()
            ->announcements()
            ->create($request->validated());

        return $this->ok($announcement, 'Announcement created', 201);
    }
}
