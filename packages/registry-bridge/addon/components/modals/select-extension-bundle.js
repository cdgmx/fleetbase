import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { action } from '@ember/object';
import { task } from 'ember-concurrency';

export default class ModalsSelectExtensionBundleComponent extends Component {
    @service store;
    @tracked extension;
    @tracked options = {};
    @tracked bundles = [];

    constructor(owner, { options }) {
        super(...arguments);
        const { extension } = options;

        this.options = options;
        this.extension = extension;
        this.loadExtensionBundles.perform(extension);
    }

    @task *loadExtensionBundles(extension) {
        this.bundles = yield this.store.query('registry-extension-bundle', { extension_uuid: extension.id, status: 'pending' });
    }

    @action selectBundle(bundle) {
        if (typeof this.options.onBundleSelected === 'function') {
            this.options.onBundleSelected(bundle);
        }
    }
}
