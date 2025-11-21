import Route from '@ember/routing/route';
import { inject as service } from '@ember/service';

export default class DevelopersExtensionsEditRoute extends Route {
    @service store;
    @service notifications;
    @service hostRouter;
    @service abilities;
    @service intl;

    beforeModel() {
        if (this.abilities.cannot('registry-bridge update extension-bundle')) {
            this.notifications.warning(this.intl.t('common.unauthorized-access'));
            return this.hostRouter.transitionTo('console');
        }
    }

    model(params) {
        return this.store.queryRecord('registry-extension', { public_id: params.public_id, single: true });
    }

    setupController(controller) {
        super.setupController(...arguments);
        const isReady = controller.validateExtensionForReview();
        if (isReady === true) {
            controller.isReady = isReady;
        }
    }
}
