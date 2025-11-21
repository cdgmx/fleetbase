import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';
import { action } from '@ember/object';
import { later } from '@ember/runloop';

export default class ModalsExtensionDetailsComponent extends Component {
    @tracked extension;
    @tracked screenshotInLightbox;

    constructor(owner, { options }) {
        super(...arguments);
        this.extension = options.extension;
    }

    @action lightboxScreenshot(screenshot) {
        this.screenshotInLightbox = screenshot;
    }

    @action setupScreenshotLightbox() {
        const listener = () => {
            this.screenshotInLightbox = undefined;
            window.removeEventListener('click', listener);
        };

        later(
            this,
            () => {
                window.addEventListener('click', listener);
            },
            600
        );
    }
}
