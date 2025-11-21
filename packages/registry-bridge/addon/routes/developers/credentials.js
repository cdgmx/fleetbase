import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';

export default class DevelopersCredentialsRoute extends Route {
    @service fetch;
    @service notifications;
    @service hostRouter;
    @service abilities;
    @service intl;

    beforeModel() {
        if (this.abilities.cannot('registry-bridge list registry-token')) {
            this.notifications.warning(this.intl.t('common.unauthorized-access'));
            return this.hostRouter.transitionTo('console');
        }
    }

    model() {
        return this.fetch.get('auth/registry-tokens', {}, { namespace: '~registry/v1' });
    }
}
