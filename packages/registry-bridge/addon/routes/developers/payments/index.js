import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';

export default class DevelopersPaymentsIndexRoute extends Route {
    @service fetch;
    @service notifications;
    @service hostRouter;
    @service abilities;
    @service intl;

    queryParams = {
        page: { refreshModel: true },
        limit: { refreshModel: true },
        sort: { refreshModel: true },
        query: { refreshModel: true },
    };

    beforeModel() {
        if (this.abilities.cannot('registry-bridge list extension-payment')) {
            this.notifications.warning(this.intl.t('common.unauthorized-access'));
            return this.hostRouter.transitionTo('console');
        }
    }

    model() {
        return this.fetch.get('payments/author-received', {}, { namespace: '~registry/v1' });
    }

    setupController(controller) {
        controller.lookupStripeConnectAccount.perform();
    }
}
