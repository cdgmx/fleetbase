import Controller from '@ember/controller';
import { action } from '@ember/object';
import { inject as service } from '@ember/service';

export default class DevelopersCredentialsController extends Controller {
    @service modalsManager;
    @service notifications;
    @service hostRouter;
    @service fetch;

    columns = [
        {
            label: 'Owner',
            valuePath: 'user.name',
            width: '15%',
        },
        {
            label: 'Fleetbase Token',
            valuePath: 'token',
            cellComponent: 'click-to-copy',
            width: '20%',
        },
        {
            label: 'Registry Token',
            valuePath: 'registry_token',
            cellComponent: 'click-to-reveal',
            cellComponentArgs: {
                clickToCopy: true,
            },
            width: '25%',
        },
        {
            label: 'Expiry',
            valuePath: 'expires_at',
            width: '15%',
        },
        {
            label: 'Created',
            valuePath: 'created_at',
            width: '15%',
        },
        {
            label: '',
            cellComponent: 'table/cell/dropdown',
            ddButtonText: false,
            ddButtonIcon: 'ellipsis-h',
            ddButtonIconPrefix: 'fas',
            ddMenuLabel: 'Credential Actions',
            cellClassNames: 'overflow-visible',
            wrapperClass: 'flex items-center justify-end mx-2',
            width: '10%',
            align: 'right',
            actions: [
                {
                    label: 'Delete Credentials',
                    fn: this.deleteCredentials,
                    className: 'text-red-700 hover:text-red-800',
                },
            ],
        },
    ];

    @action deleteCredentials(credentials) {
        this.modalsManager.confirm({
            title: 'Delete extension registry credentials?',
            body: 'Are you sure you wish to delete these credentials? Once deleted any service or user using these credentials will loose access to the registry.',
            confirm: async (modal) => {
                modal.startLoading();

                try {
                    await this.fetch.delete(`auth/registry-tokens/${credentials.uuid}`, {}, { namespace: '~registry/v1' });
                    this.notifications.success('Registry credentials deleted.');
                    return this.hostRouter.refresh();
                } catch (error) {
                    this.notifications.serverError(error);
                }
            },
        });
    }

    @action createCredentials() {
        this.modalsManager.show('modals/create-registry-credentials', {
            title: 'Create new registry credentials',
            acceptButtonText: 'Create',
            acceptButtonIcon: 'check',
            password: null,
            confirm: async (modal) => {
                modal.startLoading();

                const password = modal.getOption('password');
                if (!password) {
                    this.notifications.warning('Password cannot be empty');
                    return modal.stopLoading();
                }

                try {
                    await this.fetch.post('auth/registry-tokens', { password }, { namespace: '~registry/v1' });
                    this.notifications.success('Registry credentials created.');
                    return this.hostRouter.refresh();
                } catch (error) {
                    this.notifications.serverError(error);
                }
            },
        });
    }
}
