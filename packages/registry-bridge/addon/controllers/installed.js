import Controller from '@ember/controller';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';
import { later } from '@ember/runloop';

export default class InstalledController extends Controller {
    @service modalsManager;
    @service currentUser;
    @service notifications;
    @service socket;
    @service hostRouter;
    @service abilities;

    @action about(extension) {
        this.modalsManager.show('modals/extension-details', {
            titleComponent: 'extension-modal-title',
            modalClass: 'flb--extension-modal modal-lg',
            modalHeaderClass: 'flb--extension-modal-header',
            acceptButtonText: 'Done',
            hideDeclineButton: true,
            extension,
        });
    }

    @action uninstall(extension) {
        const uninstallChannel = `uninstall.${this.currentUser.companyId}.${extension.id}`;

        this.modalsManager.show('modals/extension-uninstall', {
            title: `Uninstall ${extension.name}`,
            modalClass: 'flb--extension-modal modal-lg',
            modalHeaderClass: 'flb--extension-modal-header',
            acceptButtonText: 'Uninstall',
            acceptButtonIcon: 'trash',
            acceptButtonScheme: 'danger',
            acceptButtonDisabled: this.abilities.cannot('registry-bridge uninstall extension'),
            process: null,
            step: null,
            stepDescription: 'Awaiting uninstall to begin...',
            progress: 0,
            extension,
            confirm: async (modal) => {
                modal.startLoading();

                // Listen for uninstall progress
                this.socket.listen(uninstallChannel, ({ process, step, progress }) => {
                    let stepDescription;
                    switch (step) {
                        case 'server.uninstall':
                            stepDescription = '(1/3) Uninstalling extension...';
                            break;

                        case 'engine.uninstall':
                            stepDescription = '(2/3) Uninstalling extension...';
                            break;

                        case 'console.build':
                            stepDescription = '(3/3) Completing uninstall...';
                            break;

                        default:
                            break;
                    }
                    modal.setOptions({ process, step, progress, stepDescription });
                });

                // Start uninstall progress
                modal.setOption('progress', 5);

                // Run uninstall
                try {
                    await extension.uninstall();
                    await this.hostRouter.refresh();
                    this.notifications.info(`${extension.name} is now Uninstalled.`);
                    later(
                        this,
                        () => {
                            window.location.reload(true);
                        },
                        600
                    );
                    modal.done();
                } catch (error) {
                    this.notifications.serverError(error);
                }
            },
        });
    }
}
