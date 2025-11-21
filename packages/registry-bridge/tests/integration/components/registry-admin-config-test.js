import { module, test } from 'qunit';
import { setupRenderingTest } from 'dummy/tests/helpers';
import { render } from '@ember/test-helpers';
import { hbs } from 'ember-cli-htmlbars';

module('Integration | Component | registry-admin-config', function (hooks) {
    setupRenderingTest(hooks);

    test('it renders', async function (assert) {
        // Set any properties with this.set('myProperty', 'value');
        // Handle any actions with this.set('myAction', function(val) { ... });

        await render(hbs`<RegistryAdminConfig />`);

        assert.dom().hasText('');

        // Template block usage:
        await render(hbs`
      <RegistryAdminConfig>
        template block text
      </RegistryAdminConfig>
    `);

        assert.dom().hasText('template block text');
    });
});
