

import Alpine from 'alpinejs';
import leaveGuard from './leave-guard';

window.Alpine = Alpine;

Alpine.data('leaveGuard', leaveGuard);

Alpine.start();
