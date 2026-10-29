/* Execute real widget update functions against a DOM sink that rejects HTML. */
const assert = require('node:assert/strict')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')

const root = path.resolve(__dirname, '../../..')
for (const version of ['dashboard', 'mobile']) {
  for (const template of ['cmd.info.string.list.html', 'cmd.action.message.additem.html']) {
    const source = fs.readFileSync(path.join(root, 'core/template', version, template), 'utf8')
    if (template === 'cmd.info.string.list.html') {
      assert(source.includes('display_value: #gkeepInitialValue#'), 'initial value must use a safe literal, not raw #state#')
      assert(!source.includes("display_value: '#state#'"), 'raw note text inside JavaScript is unsafe')
    }
    const start = source.indexOf('jeedom.cmd.addUpdateFunction(')
    const end = source.indexOf('      });', start)
    assert(start !== -1 && end !== -1, `${version}/${template}: callback missing`)
    const state = {
      textContent: '',
      dataset: {},
      style: {},
      get innerText() { return this.textContent },
      set innerHTML(_) { throw new Error('Untrusted note text inserted as HTML') }
    }
    const cmd = {
      querySelector: name => name === '.state' ? state : { checked: false },
      setAttribute() {},
      classList: { add() {}, remove() {} }
    }
    let update
    vm.runInNewContext(source.slice(start, end + '      });'.length), {
      jeedom: { cmd: { addUpdateFunction: (_, callback) => { update = callback } } },
      document: { querySelector: () => cmd },
      is_object: value => !!value
    })
    const payload = '<img src=x onerror=alert(1)>'
    update({ display_value: payload, valueDate: '', collectDate: '' })
    assert(state.textContent.includes(payload), `${version}/${template}: text not preserved`)
  }
}
console.log('GKeep widget HTML sinks: OK')
