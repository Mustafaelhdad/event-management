<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = ['user', 'event'];
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
            'user_id' => 1
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
    public function destroy(string $event, Attendee $attendee)
    {
        $attendee->delete();

        return response()->json(['message' => 'Attendee ' . $attendee->id . ' has been deleted successfully!']);
    }
}
