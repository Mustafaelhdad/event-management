<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;

class EventController extends BaseController
{
    use CanLoadRelationships;
    private readonly array $relations;

    public function __construct()
    {
        $this->relations = ['user', 'attendees', 'attendees.user'];
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Event::query();

        // foreach ($relations as $relation) {
        //     $query->when(
        //         $this->shouldIncludeRelation($relation),
        //         fn($q) => $q->with($relation)
        //     );
        // }
        $this->loadRelationships($query, $this->relations);

        return EventResource::collection($query->latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validationData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        $validationData['user_id'] = $request->user()->id;

        $event = Event::create($validationData);

        return new EventResource($this->loadRelationships(($event)));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Use the trait method to load requested relationships
        $this->loadRelationships($event, $this->relations);

        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        if (Gate::denies('update-event', $event)) {
            abort(403, 'You are not allowed to update this event');
        }

        // Gate::authorize('update-event', $event);

        $validationData = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date',
        ]);

        $event->update($validationData);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Event ' . $event->name . ' has been deleted successfully!']);
    }
}
