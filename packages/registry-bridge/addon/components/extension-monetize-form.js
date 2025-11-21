import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { task } from 'ember-concurrency';

export default class ExtensionMonetizeFormComponent extends Component {
    @service fetch;
    @tracked subscriptionModelOptions = ['flat_rate', 'tiered', 'usage'];
    @tracked billingPeriodOptions = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
    @tracked hasStripeConnectAccount = false;

    constructor() {
        super(...arguments);
        this.lookupStripeConnectAccount.perform();
    }

    @task *lookupStripeConnectAccount() {
        try {
            const { hasStripeConnectAccount } = yield this.fetch.get('payments/has-stripe-connect-account', {}, { namespace: '~registry/v1' });
            this.hasStripeConnectAccount = hasStripeConnectAccount;
        } catch (error) {
            this.hasStripeConnectAccount = false;
        }
    }
}
