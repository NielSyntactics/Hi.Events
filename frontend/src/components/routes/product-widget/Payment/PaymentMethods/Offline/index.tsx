import {Event} from "../../../../../../types.ts";
import {Card} from "../../../../../common/Card";
import {t} from "@lingui/macro";
import {Group, Image, Text, ActionIcon, Stack, rem} from "@mantine/core";
import {IconUpload, IconX, IconTrash} from "@tabler/icons-react";
import {Dropzone, IMAGE_MIME_TYPE} from "@mantine/dropzone";
import {useEffect, useState} from "react";

interface OfflinePaymentMethodProps {
    event: Event;
    onFileChange?: (file: File | null) => void;
}

export const OfflinePaymentMethod = ({event, onFileChange}: OfflinePaymentMethodProps) => {
    const eventSettings = event?.settings;
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        return () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        };
    }, [previewUrl]);

    const handleDrop = (files: File[]) => {
        const file = files[0];
        if (!file) return;

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        const url = URL.createObjectURL(file);
        setPreviewUrl(url);
        onFileChange?.(file);
    };

    const handleRemove = () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        setPreviewUrl(null);
        onFileChange?.(null);
    };

    return (
        <div>
            <h2>{t`Payment Instructions`}</h2>
            <Card>
                <div
                    dangerouslySetInnerHTML={{
                        __html: eventSettings?.offline_payment_instructions || "",
                    }}
                />
            </Card>

            <div style={{marginTop: '1rem'}}>
                <h3>{t`Upload Payment Receipt`}</h3>
                <Text size="sm" c="dimmed" style={{marginBottom: '0.5rem'}}>
                    {t`Upload a screenshot or photo of your payment confirmation.`}
                </Text>

                {previewUrl ? (
                    <Card>
                        <Stack gap="sm">
                            <Image
                                src={previewUrl}
                                alt={t`Payment receipt`}
                                radius="sm"
                                fit="contain"
                                h={200}
                            />
                            <Group justify="space-between">
                                <Text size="sm" c="dimmed">{t`Receipt selected`}</Text>
                                <ActionIcon
                                    variant="subtle"
                                    color="red"
                                    size="sm"
                                    onClick={handleRemove}
                                >
                                    <IconTrash size={16}/>
                                </ActionIcon>
                            </Group>
                        </Stack>
                    </Card>
                ) : (
                    <Dropzone
                        onDrop={handleDrop}
                        maxFiles={1}
                        maxSize={8 * 1024 * 1024}
                        accept={IMAGE_MIME_TYPE}
                    >
                        <Group justify="center" gap="xl" style={{minHeight: rem(80), pointerEvents: 'none'}}>
                            <Dropzone.Accept>
                                <IconUpload size={rem(40)} stroke={1.5}/>
                            </Dropzone.Accept>
                            <Dropzone.Reject>
                                <IconX size={rem(40)} stroke={1.5}/>
                            </Dropzone.Reject>
                            <Dropzone.Idle>
                                <IconUpload size={rem(40)} stroke={1.5}/>
                            </Dropzone.Idle>

                            <div>
                                <Text size="sm">
                                    {t`Drag an image here or click to select`}
                                </Text>
                                <Text size="xs" c="dimmed">
                                    {t`JPEG, PNG, or WebP, max 8MB`}
                                </Text>
                            </div>
                        </Group>
                    </Dropzone>
                )}
            </div>
        </div>
    );
};
