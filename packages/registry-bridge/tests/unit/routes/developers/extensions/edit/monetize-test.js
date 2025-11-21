import { module, test } from 'qunit';
import { setupTest } from 'dummy/tests/helpers';

module('Unit | Route | developers/extensions/edit/monetize', function (hooks) {
    setupTest(hooks);

    test('it exists', function (assert) {
        let route = this.owner.lookup('route:developers/extensions/edit/monetize');
        assert.ok(route);
    });
});
