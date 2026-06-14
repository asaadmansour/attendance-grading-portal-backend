<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $direction = $request->query('sort') === 'oldest' ? 'asc' : 'desc';

        $announcements = Announcement::query()
            ->with('author:id,name')
            ->orderBy('created_at', $direction)
            ->paginate($perPage);

        return $this->ok([
            'items' => $announcements->items(),
            'meta' => [
                'page' => $announcements->currentPage(),
                'per_page' => $announcements->perPage(),
                'total' => $announcements->total(),
                'last_page' => $announcements->lastPage(),
            ],
        ]);
    }

    public function show(Announcement $announcement)
    {
        return $this->ok($announcement->load('author:id,name'));
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $announcement = $request->user()
            ->announcements()
            ->create($request->validated());

        // match index() shape so the client can render the new post without a refresh
        return $this->ok($announcement->load('author:id,name'), 'Announcement created', 201);
    }
}
