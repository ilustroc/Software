export function sidebarLayout(config = {}) {
    return {
        mobileOpen: false,
        openGestiones: !!config.openGestiones,
        openPagos: !!config.openPagos,
        openReportes: !!config.openReportes,

        closeSidebar() {
            this.mobileOpen = false;
        },

        toggle(section) {
            this[section] = !this[section];
        },
    };
}