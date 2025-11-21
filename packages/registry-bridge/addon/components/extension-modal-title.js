import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';

export default class ExtensionModalTitleComponent extends Component {
    @tracked extension;
    @tracked detailsContainerClass = 'mb-4';

    constructor(owner, { options, extension = null, detailsContainerClass = 'mb-4' }) {
        super(...arguments);
        this.extension = options ? options.extension : extension;
        this.detailsContainerClass = detailsContainerClass;
    }

    @action handleExtensionChanged(el, [extension]) {
        if (extension) {
            this.extension = extension;
        }
    }
}
