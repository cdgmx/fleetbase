import Controller from '@ember/controller';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';
import { task } from 'ember-concurrency';

export default class DevelopersAnalyticsController extends Controller {
    @service fetch;
    @service notifications;
    @tracked selectedExtension;
    @tracked extensions = [];
    @tracked metrics = {};

    @action selectExtension(extension) {
        this.selectedExtension = extension;
        this.getExtensionAnalytics.perform(extension);
    }

    @task *getExtensionAnalytics(extension) {
        try {
            this.metrics = yield this.fetch.get('registry-extensions/analytics', { id: extension.id }, { namespace: '~registry/v1' });
        } catch (error) {
            this.notifications.serverError(error);
        }
    }
}
