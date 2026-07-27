import {useGetEventPublic} from "../../../../queries/useGetEventPublic.ts";
import {useParams} from "react-router";
import {useGetAttendeePublic} from "../../../../queries/useGetAttendeePublic.ts";
import {Attendee, Product} from "../../../../types.ts";
import {Container, Button, Group, Text} from "@mantine/core";
import {t} from "@lingui/macro";
import {PoweredByFooter} from "../../../common/PoweredByFooter";
import {OnlineEventDetails} from "../../../common/OnlineEventDetails";
import {HomepageInfoMessage} from "../../../common/HomepageInfoMessage";
import classes from './AttendeeProductAndInformation.module.scss';
import {useGetMe} from "../../../../queries/useGetMe.ts";
import {useGetEvent} from "../../../../queries/useGetEvent.ts";
import {useGetOrderPublic} from "../../../../queries/useGetOrderPublic.ts";
import {useMarkOrderAsPaid} from "../../../../mutations/useMarkOrderAsPaid.ts";
import {showSuccess, showError} from "../../../../utilites/notifications.tsx";
import {confirmationDialog} from "../../../../utilites/confirmationDialog.tsx";
import {IconCheck, IconExternalLink, IconReceipt} from "@tabler/icons-react";
import {AttendeeTicket} from "../../../common/AttendeeTicket";
import {useState} from "react";
import {ImageViewer} from "../../../common/ImageViewer";
import {useDisclosure} from "@mantine/hooks";

export const AttendeeProductAndInformation = () => {
    const {eventId, attendeeShortId} = useParams();
    const {data: event, isError: eventError} = useGetEventPublic(eventId);
    const {data: attendee, isError: attendeeError} = useGetAttendeePublic(eventId, String(attendeeShortId));

    // Admin detection
    const {data: currentUser} = useGetMe();
    const isLoggedIn = !!currentUser;
    const {data: managedEvent} = useGetEvent(isLoggedIn ? eventId : undefined);
    const isAdmin = isLoggedIn && !!managedEvent;

    // Fetch order data if admin and order_short_id is available
    const orderShortId = attendee?.order_short_id;
    const {data: order} = useGetOrderPublic(
        isAdmin ? eventId : undefined,
        isAdmin ? orderShortId : undefined,
        ['event']
    );

    // Mark as paid mutation
    const markAsPaidMutation = useMarkOrderAsPaid();

    // Image viewer for receipt
    const [imageViewerOpened, {open: openImageViewer, close: closeImageViewer}] = useDisclosure(false);
    const [selectedImageUrl, setSelectedImageUrl] = useState<string | null>(null);

    const handleMarkAsPaid = () => {
        if (!order) {
            return;
        }

        confirmationDialog(
            t`Are you sure you want to mark this order as paid?`,
            () => {
                markAsPaidMutation.mutate(
                    {eventId: String(eventId), orderId: String(order.id)},
                    {
                        onSuccess: () => {
                            showSuccess(t`Order has been marked as paid successfully.`);
                            window.location.reload();
                        },
                        onError: () => {
                            showError(t`Failed to mark order as paid. Please try again.`);
                        },
                    }
                );
            },
            {confirm: t`Mark as Paid`}
        );
    };

    if (eventError || attendeeError) {
        return (
            <HomepageInfoMessage
                status="not_found"
                message={t`Ticket Not Found`}
                subtitle={t`We couldn't find the ticket you're looking for. The link may have expired or the ticket details may have changed.`}
            />
        );
    }

    if (!event || !attendee) {
        return null;
    }

    const isAwaitingOfflinePayment = order?.status === 'AWAITING_OFFLINE_PAYMENT';
    const hasReceipt = !!order?.payment_receipt_url;

    return (
        <Container>
            <h2 className={classes.title}>{t`Your ticket for`} {event.title}</h2>

            <AttendeeTicket
                attendee={attendee as Attendee}
                product={attendee.product as Product}
                event={event}
                showPoweredBy
            />

            {(event?.settings?.is_online_event && <OnlineEventDetails eventSettings={event.settings}/>)}

            {/* Admin Section */}
            {isAdmin && order && (
                <div className={classes.adminSection}>
                    <div className={classes.adminHeader}>
                        <Text fw={600} size="lg">{t`Admin Actions`}</Text>
                        <Text size="sm" c="dimmed">{t`You are viewing this ticket as an event administrator.`}</Text>
                    </div>

                    {/* Payment Status */}
                    {isAwaitingOfflinePayment && (
                        <div className={classes.adminCard}>
                            <Group justify="space-between" align="center">
                                <div>
                                    <Text fw={500}>{t`Payment Status`}</Text>
                                    <Text size="sm" c="dimmed">{t`This order is awaiting offline payment.`}</Text>
                                </div>
                                <Button
                                    color="green"
                                    leftSection={<IconCheck size={16}/>}
                                    onClick={handleMarkAsPaid}
                                    loading={markAsPaidMutation.isPending}
                                >
                                    {t`Mark as Paid`}
                                </Button>
                            </Group>
                        </div>
                    )}

                    {/* Payment Receipt */}
                    {hasReceipt && (
                        <div className={classes.adminCard}>
                            <Group justify="space-between" align="center">
                                <div>
                                    <Text fw={500}>{t`Payment Receipt`}</Text>
                                    <Text size="sm" c="dimmed">{t`View the uploaded payment receipt.`}</Text>
                                </div>
                                <Button
                                    variant="light"
                                    leftSection={<IconReceipt size={16}/>}
                                    onClick={() => {
                                        setSelectedImageUrl(order.payment_receipt_url ?? null);
                                        openImageViewer();
                                    }}
                                >
                                    {t`View Receipt`}
                                </Button>
                            </Group>
                        </div>
                    )}

                    {/* Orders Page Link */}
                    <div className={classes.adminCard}>
                        <Group justify="space-between" align="center">
                            <div>
                                <Text fw={500}>{t`Manage Orders`}</Text>
                                <Text size="sm" c="dimmed">{t`Go to the orders page for this event.`}</Text>
                            </div>
                            <Button
                                variant="light"
                                component="a"
                                href={`/manage/event/${eventId}/orders`}
                                leftSection={<IconExternalLink size={16}/>}
                            >
                                {t`View Orders`}
                            </Button>
                        </Group>
                    </div>
                </div>
            )}

            <PoweredByFooter/>

            <ImageViewer
                opened={imageViewerOpened}
                onClose={closeImageViewer}
                src={selectedImageUrl || ''}
                alt={t`Payment receipt`}
            />
        </Container>
    )
}

export default AttendeeProductAndInformation;
