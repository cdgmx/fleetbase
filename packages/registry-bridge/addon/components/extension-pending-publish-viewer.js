import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';
import { task } from 'ember-concurrency';

export default class ExtensionPendingPublishViewerComponent extends Component {
    @service store;
    @service notifications;
    @tracked extensions = [];
    @tracked focusedExtension;

    constructor() {
        super(...arguments);
        this.getExtensionsPendingPublish.perform();
    }

    @task *getExtensionsPendingPublish() {
        this.extensions = yield this.store.query('registry-extension', { status: 'approved', admin: 1 });
    }

    @task *downloadBundle(extension) {
        try {
            yield extension.downloadBundle();
        } catch (error) {
            this.notifications.error(error.message);
        }
    }

    @task *publishExtension(extension) {
        try {
            yield extension.publish();
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
}
