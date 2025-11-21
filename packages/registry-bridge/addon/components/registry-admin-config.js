import Component from '@glimmer/component';
import { inject as service } from '@ember/service';
import { tracked } from '@glimmer/tracking';
import { task } from 'ember-concurrency';

export default class RegistryAdminConfigComponent extends Component {
    @service fetch;
    @service notifications;
    @tracked registryHost;
    @tracked registryToken;

    constructor() {
        super(...arguments);
        this.getConfigValues.perform();
    }

    @task *getConfigValues() {
        try {
            const { host, token } = yield this.fetch.get('registry-extensions/config', {}, { namespace: '~registry/v1' });
            this.registryHost = host;
            this.registryToken = token;
        } catch (error) {
            this.notifications.serverError(error);
        }
    }

    @task *saveConfigValues() {
        try {
            const { host, token } = yield this.fetch.post('registry-extensions/config', { host: this.registryHost, token: this.registryToken }, { namespace: '~registry/v1' });
            this.registryHost = host;
            this.registryToken = token;
        } catch (error) {
            this.notifications.serverError(error);
        }
    }
}
