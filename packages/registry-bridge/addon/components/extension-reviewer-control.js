import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';
import { task } from 'ember-concurrency';

export default class ExtensionReviewerControlComponent extends Component {
    @service store;
    @service modalsManager;
    @service notifications;
    @service intl;
    @tracked extensions = [];
    @tracked focusedExtension;

    constructor() {
        super(...arguments);
        this.getExtensionsPendingReview.perform();
    }

    @task *getExtensionsPendingReview() {
        this.extensions = yield this.store.query('registry-extension', { status: 'awaiting_review', admin: 1 });
    }

    @task *downloadBundle(extension) {
        try {
            yield extension.downloadBundle();
        } catch (error) {
            this.notifications.error(error.message);
        }
    }

    @action focusExtension(extension) {
        this.focusedExtension = extension;
    }

    @action unfocusExtension() {
        this.focusedExtension = undefined;
    }

    @action approve(extension) {
        return this.modalsManager.confirm({
            title: this.intl.t('registry-bridge.component.extension-reviewer-control.approve-confirm-title', { extensionName: extension.name }),
            body: this.intl.t('registry-bridge.component.extension-reviewer-control.approve-confirm-body'),
            acceptButtonText: this.intl.t('registry-bridge.component.extension-reviewer-control.approve'),
            confirm: () => {
                this.unfocusExtension();
                return extension.approve().finally(() => {
                    this.getExtensionsPendingReview.perform();
                });
            },
        });
    }

    @action reject(extension) {
        return this.modalsManager.confirm({
            title: this.intl.t('registry-bridge.component.extension-reviewer-control.decline-confirm-title', { extensionName: extension.name }),
            body: this.intl.t('registry-bridge.component.extension-reviewer-control.decline-confirm-body'),
            acceptButtonText: this.intl.t('registry-bridge.component.extension-reviewer-control.reject'),
            acceptButtonIcon: 'ban',
            acceptButtonScheme: 'danger',
            confirm: () => {
                return extension.reject().finally(() => {
                    this.getExtensionsPendingReview.perform();
                });
            },
        });
    }
}
