import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Tile38 Field Console';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: () => null,
    progress: {
        color: '#2dd4bf',
    },
});

// The showcase is dark-first; keep the survey ground even if the OS prefers light.
localStorage.setItem('theme', 'dark');
document.documentElement.classList.add('dark');

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
