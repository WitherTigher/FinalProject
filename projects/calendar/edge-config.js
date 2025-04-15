import { get, set } from '@vercel/edge-config';

export async function getEvents() {
  try {
    const events = await get('calendar_events');
    return events || [];
  } catch (error) {
    console.error('Error fetching events:', error);
    return [];
  }
}

export async function addEvent(event) {
  try {
    const events = await getEvents();
    const newEvents = [...events, { ...event, id: Date.now().toString() }];
    await set('calendar_events', newEvents);
    return newEvents;
  } catch (error) {
    console.error('Error adding event:', error);
    throw error;
  }
}

export async function updateEvent(eventId, updatedEvent) {
  try {
    const events = await getEvents();
    const newEvents = events.map(event => 
      event.id === eventId ? { ...event, ...updatedEvent } : event
    );
    await set('calendar_events', newEvents);
    return newEvents;
  } catch (error) {
    console.error('Error updating event:', error);
    throw error;
  }
}

export async function deleteEvent(eventId) {
  try {
    const events = await getEvents();
    const newEvents = events.filter(event => event.id !== eventId);
    await set('calendar_events', newEvents);
    return newEvents;
  } catch (error) {
    console.error('Error deleting event:', error);
    throw error;
  }
} 