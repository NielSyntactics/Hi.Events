import {GenericDataResponse, IdParam, Image, ImageType} from "../types.ts";
import {api} from "./client.ts";
import {publicApi} from "./public-client.ts";

export const imageClient = {
    uploadImage: async (image: File, imageType?: ImageType, entityId?: IdParam) => {
        const formData = new FormData();
        formData.append('image', image);
        if (imageType) {
            formData.append('image_type', imageType);
        }
        if (entityId) {
            formData.append('entity_id', entityId as string);
        }
        const response = await api.post<GenericDataResponse<Image>>('images', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response.data;
    },
    delete: async (imageId: IdParam) => {
        const response = await api.delete(`images/${imageId}`);
        return response.data;
    },
    uploadPaymentReceipt: async (eventId: IdParam, orderShortId: IdParam, image: File) => {
        const formData = new FormData();
        formData.append('image', image);
        const response = await publicApi.post<{ data: { url: string } }>(
            `events/${eventId}/order/${orderShortId}/payment-receipt`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        return response.data;
    },
}
