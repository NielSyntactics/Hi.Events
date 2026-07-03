import {useLoaderData, useParams} from "react-router";
import {LoadingOverlay} from "@mantine/core";
import EventHomepage from "../EventHomepage";
import {EventNotAvailable} from "../EventHomepage/EventNotAvailable";
import {Event} from "../../../types";
import {useGetEventPublic} from "../../../queries/useGetEventPublic.ts";

export const PublicEvent = () => {
    const loaderData = useLoaderData();
    const {eventId} = useParams();

    const {event: loaderEvent, promoCodeValid, promoCode} = loaderData as {
        event?: Event;
        promoCodeValid?: boolean;
        promoCode?: string;
    };

    // The loader runs server-side without the user's auth cookie, so it can't
    // fetch draft events. Fall back to a client-side fetch (where the owner is
    // authenticated) when the loader returns nothing.
    const clientQuery = useGetEventPublic(eventId, !loaderEvent);

    const event = loaderEvent ?? clientQuery.data;

    // Only mount EventHomepage once we have an event. It must never mount with a
    // null event and later receive one - it calls hooks after an early null-guard
    // return, so a null -> defined transition changes hook order and crashes.
    if (!event) {
        if (!loaderEvent && !clientQuery.isFetched) {
            return <LoadingOverlay visible/>;
        }
        return <EventNotAvailable/>;
    }

    return (
        <EventHomepage
            event={event}
            promoCodeValid={promoCodeValid}
            promoCode={promoCode}
        />
    );
};

export default PublicEvent;
