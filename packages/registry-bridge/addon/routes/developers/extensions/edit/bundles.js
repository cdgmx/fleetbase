import Route from '@ember/routing/route';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';

export default class DevelopersExtensionsEditBundlesRoute extends Route {
    @service store;
    @tracked extension;

    beforeModel() {
        this.extension = this.modelFor('developers.extensions.edit');
    }

    model() {
        return this.store.query('registry-extension-bundle', { extension_uuid: this.extension.id });
    }

    setupController(controller) {
        super.setupController(...arguments);
        controller.extension = this.extension;
    }
}
