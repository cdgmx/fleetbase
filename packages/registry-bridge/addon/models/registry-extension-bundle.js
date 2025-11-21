import Model, { attr, belongsTo } from '@ember-data/model';
import { computed } from '@ember/object';
import { format as formatDate, formatDistanceToNow, isValid as isValidDate } from 'date-fns';

export default class RegistryExtensionBundleModel extends Model {
    /** @ids */
    @attr('string') uuid;
    @attr('string') company_uuid;
    @attr('string') created_by_uuid;
    @attr('string') extension_uuid;
    @attr('string') bundle_uuid;
    @attr('string') bundle_id;
    @attr('string') public_id;

    /** @relationships */
    @belongsTo('file') bundle;

    /** @attributes */
    @attr('string') bundle_filename;
    @attr('string') bundle_number;
    @attr('string') version;
    @attr('string') status;
    @attr('object') meta;

    /** @dates */
    @attr('date') created_at;
    @attr('date') updated_at;
    @attr('date') deleted_at;

    /** @computed */
    @computed('updated_at') get updatedAgo() {
        if (!isValidDate(this.updated_at)) {
            return null;
        }

        return formatDistanceToNow(this.updated_at);
    }

    @computed('updated_at') get updatedAt() {
        if (!isValidDate(this.updated_at)) {
            return null;
        }

        return formatDate(this.updated_at, 'PP HH:mm');
    }

    @computed('created_at') get createdAgo() {
        if (!isValidDate(this.created_at)) {
            return null;
        }

        return formatDistanceToNow(this.created_at);
    }

    @computed('created_at') get createdAt() {
        if (!isValidDate(this.created_at)) {
            return null;
        }

        return formatDate(this.created_at, 'PP HH:mm');
    }
}
