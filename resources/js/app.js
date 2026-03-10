import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { sidebarLayout } from './modules/sidebar';

window.Alpine = Alpine;

Alpine.plugin(collapse);
Alpine.data('sidebarLayout', sidebarLayout);

Alpine.start();