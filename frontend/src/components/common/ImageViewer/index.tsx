import React, {useCallback, useEffect, useRef, useState} from "react";
import {createPortal} from "react-dom";
import {ActionIcon, Group, Text, Tooltip} from "@mantine/core";
import {IconAlertCircle, IconMinus, IconPlus, IconX, IconZoomIn} from "@tabler/icons-react";
import {t} from "@lingui/macro";
import classes from "./ImageViewer.module.scss";

interface ImageViewerProps {
    opened: boolean;
    onClose: () => void;
    src: string;
    alt?: string;
}

const MIN_ZOOM = 0.5;
const MAX_ZOOM = 5;
const ZOOM_STEP = 0.25;
const BOUND_PADDING = 50;

const clampPosition = (x: number, y: number, zoom: number, viewportWidth: number, viewportHeight: number) => {
    const maxX = Math.max(0, (viewportWidth * (zoom - 1)) / 2 + BOUND_PADDING);
    const maxY = Math.max(0, (viewportHeight * (zoom - 1)) / 2 + BOUND_PADDING);
    return {
        x: Math.max(-maxX, Math.min(maxX, x)),
        y: Math.max(-maxY, Math.min(maxY, y)),
    };
};

export const ImageViewer = ({opened, onClose, src, alt}: ImageViewerProps) => {
    const [zoom, setZoom] = useState(1);
    const [position, setPosition] = useState({x: 0, y: 0});
    const [isDragging, setIsDragging] = useState(false);
    const [dragStart, setDragStart] = useState({x: 0, y: 0});
    const [showHint, setShowHint] = useState(true);
    const containerRef = useRef<HTMLDivElement>(null);
    const imageRef = useRef<HTMLImageElement>(null);

    useEffect(() => {
        if (opened) {
            setZoom(1);
            setPosition({x: 0, y: 0});
            setShowHint(true);
            const timer = setTimeout(() => setShowHint(false), 3000);
            return () => clearTimeout(timer);
        }
    }, [opened]);

    const handleWheel = useCallback((e: React.WheelEvent) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -ZOOM_STEP : ZOOM_STEP;
        setZoom(prev => {
            const newZoom = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, prev + delta));
            if (newZoom <= 1) {
                setPosition({x: 0, y: 0});
            } else if (containerRef.current) {
                const {width, height} = containerRef.current.getBoundingClientRect();
                setPosition(pos => clampPosition(pos.x, pos.y, newZoom, width, height));
            }
            return newZoom;
        });
    }, []);

    const handleMouseDown = useCallback((e: React.MouseEvent) => {
        if (zoom > 1) {
            setIsDragging(true);
            setDragStart({x: e.clientX - position.x, y: e.clientY - position.y});
        }
    }, [zoom, position]);

    const handleMouseMove = useCallback((e: React.MouseEvent) => {
        if (isDragging && zoom > 1 && containerRef.current) {
            const {width, height} = containerRef.current.getBoundingClientRect();
            const rawX = e.clientX - dragStart.x;
            const rawY = e.clientY - dragStart.y;
            setPosition(clampPosition(rawX, rawY, zoom, width, height));
        }
    }, [isDragging, dragStart, zoom]);

    const handleMouseUp = useCallback(() => {
        setIsDragging(false);
    }, []);

    const handleDoubleClick = useCallback(() => {
        if (zoom > 1) {
            setZoom(1);
            setPosition({x: 0, y: 0});
        } else {
            setZoom(2.5);
        }
    }, [zoom]);

    const handleZoomIn = useCallback(() => {
        setZoom(prev => Math.min(MAX_ZOOM, prev + ZOOM_STEP));
    }, []);

    const handleZoomOut = useCallback(() => {
        setZoom(prev => {
            const newZoom = Math.max(MIN_ZOOM, prev - ZOOM_STEP);
            if (newZoom <= 1) {
                setPosition({x: 0, y: 0});
            } else if (containerRef.current) {
                const {width, height} = containerRef.current.getBoundingClientRect();
                setPosition(pos => clampPosition(pos.x, pos.y, newZoom, width, height));
            }
            return newZoom;
        });
    }, []);

    const handleReset = useCallback(() => {
        setZoom(1);
        setPosition({x: 0, y: 0});
    }, []);

    useEffect(() => {
        if (!opened) return;

        const handleKeyDown = (e: KeyboardEvent) => {
            switch (e.key) {
                case 'Escape':
                    onClose();
                    break;
                case '+':
                case '=':
                    handleZoomIn();
                    break;
                case '-':
                    handleZoomOut();
                    break;
                case '0':
                    handleReset();
                    break;
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [opened, onClose, handleZoomIn, handleZoomOut, handleReset]);

    if (!opened) return null;

    if (!src) {
        return createPortal(
            <div className={classes.overlay}>
                <div className={classes.errorState}>
                    <IconAlertCircle size={48} stroke={1.5}/>
                    <Text size="lg" fw={500}>{t`No image available`}</Text>
                </div>
                <Tooltip label={t`Close (Esc)`} position="bottom">
                    <ActionIcon
                        variant="filled"
                        color="dark"
                        size="xl"
                        onClick={onClose}
                        className={classes.closeButton}
                    >
                        <IconX size={22}/>
                    </ActionIcon>
                </Tooltip>
            </div>,
            document.body
        );
    }

    const zoomPercent = Math.round(zoom * 100);

    return createPortal(
        <div
            ref={containerRef}
            className={classes.overlay}
            onWheel={handleWheel}
            onMouseDown={handleMouseDown}
            onMouseMove={handleMouseMove}
            onMouseUp={handleMouseUp}
            onMouseLeave={handleMouseUp}
            onDoubleClick={handleDoubleClick}
        >
            <div className={classes.imageWrapper}>
                <img
                    ref={imageRef}
                    src={src}
                    alt={alt || t`Payment receipt`}
                    className={classes.image}
                    style={{
                        transform: `translate(${position.x}px, ${position.y}px) scale(${zoom})`,
                        cursor: zoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in',
                    }}
                    draggable={false}
                />
            </div>

            {showHint && (
                <div className={classes.hint}>
                    <IconZoomIn size={16}/>
                    <Text size="sm">{t`Scroll to zoom • Double-click to toggle • Drag to pan`}</Text>
                </div>
            )}

            <div className={classes.toolbar}>
                <Group gap="xs">
                    <Tooltip label={t`Zoom out (-)`} position="bottom">
                        <ActionIcon
                            variant="filled"
                            color="dark"
                            size="lg"
                            onClick={handleZoomOut}
                            disabled={zoom <= MIN_ZOOM}
                            className={classes.toolButton}
                        >
                            <IconMinus size={18}/>
                        </ActionIcon>
                    </Tooltip>

                    <div className={classes.zoomBadge} onClick={handleReset}>
                        <Text size="xs" fw={600}>{zoomPercent}%</Text>
                    </div>

                    <Tooltip label={t`Zoom in (+)`} position="bottom">
                        <ActionIcon
                            variant="filled"
                            color="dark"
                            size="lg"
                            onClick={handleZoomIn}
                            disabled={zoom >= MAX_ZOOM}
                            className={classes.toolButton}
                        >
                            <IconPlus size={18}/>
                        </ActionIcon>
                    </Tooltip>
                </Group>
            </div>

            <Tooltip label={t`Close (Esc)`} position="bottom">
                <ActionIcon
                    variant="filled"
                    color="dark"
                    size="xl"
                    onClick={onClose}
                    className={classes.closeButton}
                >
                    <IconX size={22}/>
                </ActionIcon>
            </Tooltip>
        </div>,
        document.body
    );
};
