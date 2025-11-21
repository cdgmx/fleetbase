import Component from '@glimmer/component';
import { tracked } from '@glimmer/tracking';

export default class ModalsExtensionUninstallComponent extends Component {
    @tracked extension;

    constructor(owner, { options }) {
        super(...arguments);
        this.extension = options.extension;
    }
}
