import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';

export default class InstalledRoute extends Route {
    @service fetch;
    @service notifications;
    @service hostRouter;
    @service abilities;
    @service intl;

    beforeModel() {
        if (this.abilities.cannot('registry-bridge list extension-install')) {
            this.notifications.warning(this.intl.t('common.unauthorized-access'));
            return this.hostRouter.transitionTo('console');
        }
    }

    model(params = {}) {
        return this.fetch.get('registry-extensions/installed', params, { namespace: '~registry/v1', normalizeToEmberData: true, modelType: 'registry-extension' });
    }
}
