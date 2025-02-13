<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controller as BaseController;

class AttendeeController extends BaseController
{
    use AuthorizesRequests;
    use CanLoadRelationships;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = ['user', 'event'];
        $this->middleware('auth:sanctum')->except(['index', 'show', 'update']);
        $this->middleware('throttle:60,1')->only(['store', 'update', 'destroy']);
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        // Get attendees for the event
        $attendees = $event->attendees()->latest();

        // Apply dynamic relationship loading
        $this->loadRelationships($attendees, $this->relations);

        return AttendeeResource::collection($attendees->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $attendee = $event->attendees()->create([
            'user_id' => $request->user()->id,
        ]);

        return new AttendeeResource($this->loadRelationships($attendee, $this->relations));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, $attendeeId)
    {
        // Find the attendee within the event
        $attendee = $event->attendees()->findOrFail($attendeeId);

        // Apply relationship loading
        $this->loadRelationships($attendee, $this->relations);

        return new AttendeeResource($attendee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event, Attendee $attendee)
    {
        $validationData = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $attendee->update($validationData);

        return new AttendeeResource($this->loadRelationships($attendee, $this->relations));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {

        // if (Gate::denies('delete-attendee', [$event, $attendee])) {
        //     abort(403, 'Unauthorized action.');
        // }

        $attendee->delete();

        return response()->json(['message' => 'Attendee ' . $attendee->id . ' has been deleted successfully!']);
    }
}
