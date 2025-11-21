import Controller from '@ember/controller';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';

export default class PurchasedController extends Controller {
    @service modalsManager;

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
}
