import Controller from '@ember/controller';
import { tracked } from '@glimmer/tracking';
import { inject as service } from '@ember/service';
import { task } from 'ember-concurrency';

export default class DevelopersExtensionsEditBundlesController extends Controller {
    @service store;
    @service fetch;
    @service hostRouter;
    @service notifications;
    @tracked extension;
    @tracked lastError;
    acceptedBundleTypes = [
        'application/zip',
        'application/x-zip',
        'application/x-zip-compressed',
        'application/x-compressed',
        'multipart/x-zip',
        'application/x-tar',
        'application/gzip',
        'application/x-gzip',
        'application/x-tgz',
        'application/x-bzip2',
        'application/x-xz',
    ];

    @task *uploadBundle(file) {
        this.lastError = undefined;

        yield this.fetch.uploadFile.perform(
            file,
            {
                path: `uploads/extensions/${this.extension.id}/bundles`,
                subject_uuid: this.extension.id,
                subject_type: 'registry-bridge:registry-extension',
                type: 'extension_bundle',
                meta: {
                    version: this.extension.version,
                },
            },
            (uploadedFile) => {
                return this.createBundle.perform(uploadedFile);
            },
            () => {
                // remove file from queue
                if (file.queue && typeof file.queue.remove === 'function') {
                    file.queue.remove(file);
                }
            }
        );
    }

    @task *createBundle(uploadedFile) {
        const bundle = this.store.createRecord('registry-extension-bundle', {
            extension_uuid: this.extension.id,
            bundle_uuid: uploadedFile.id,
            bundle: uploadedFile,
            status: 'pending',
        });

        try {
            yield bundle.save();
        } catch (error) {
            this.lastError = error.message;
            return this.notifications.serverError(error);
        }

        yield this.hostRouter.refresh();
    }
}
