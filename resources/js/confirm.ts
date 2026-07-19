import { ref } from 'vue';

export interface ConfirmState {
    isOpen: boolean;
    message: string;
    isAlert?: boolean;
    title?: string;
    onConfirm: (() => void) | null;
    onCancel: (() => void) | null;
}

export const confirmState = ref<ConfirmState>({
    isOpen: false,
    message: '',
    isAlert: false,
    title: '',
    onConfirm: null,
    onCancel: null,
});

export const showConfirm = (message: string): Promise<boolean> => {
    return new Promise<boolean>((resolve) => {
        confirmState.value = {
            isOpen: true,
            message,
            isAlert: false,
            title: 'Konfirmasi Tindakan',
            onConfirm: () => {
                confirmState.value.isOpen = false;
                resolve(true);
            },
            onCancel: () => {
                confirmState.value.isOpen = false;
                resolve(false);
            },
        };
    });
};

export const showAlert = (message: string, title: string = 'Peringatan'): Promise<void> => {
    return new Promise<void>((resolve) => {
        confirmState.value = {
            isOpen: true,
            message,
            isAlert: true,
            title,
            onConfirm: () => {
                confirmState.value.isOpen = false;
                resolve();
            },
            onCancel: () => {
                confirmState.value.isOpen = false;
                resolve();
            },
        };
    });
};

if (typeof window !== 'undefined') {
    (window as any).showConfirm = showConfirm;
    (window as any).showAlert = showAlert;
}
