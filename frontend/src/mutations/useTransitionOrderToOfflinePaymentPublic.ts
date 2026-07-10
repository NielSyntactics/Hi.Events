import {orderClientPublic} from "../api/order.client.ts";
import {IdParam} from "../types.ts";
import {useMutation} from "@tanstack/react-query";

export const useTransitionOrderToOfflinePaymentPublic = () => {
    return useMutation({
        mutationFn: ({eventId, orderShortId, paymentReceiptUrl}: {
            eventId: IdParam,
            orderShortId: IdParam,
            paymentReceiptUrl?: string,
        }) => {
            return orderClientPublic.transitionToOfflinePayment(eventId, orderShortId, paymentReceiptUrl);
        }
    });
}
