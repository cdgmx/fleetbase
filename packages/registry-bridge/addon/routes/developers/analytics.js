import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';

export default class DevelopersAnalyticsRoute extends Route {
    @service store;
    @service notifications;
    @service hostRouter;
    @service abilities;
    @service intl;

    beforeModel() {
        if (this.abilities.cannot('registry-bridge list extension-analytic')) {
            this.notifications.warning(this.intl.t('common.unauthorized-access'));
            return this.hostRouter.transitionTo('console');
        }
    }

    model() {
        return this.store.query('registry-extension', { is_author: 1 });
    }
}
