/*!
* rete-module-plugin v0.4.1 
* (c) 2020 Vitaliy Stoliarov 
* Released under the MIT license.
*/
import { Input, Output } from 'rete';

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function extractNodes(nodes, map) {
  var names = Array.from(map.keys());
  return Object.keys(nodes).filter(function (k) {
    return names.includes(nodes[k].name);
  }).map(function (k) {
    return nodes[k];
  }).sort(function (n1, n2) {
    return n1.position[1] > n2.position[1];
  });
}
function removeIO(node, editor) {
  node.getConnections().forEach(function (c) {
    return editor.removeConnection(c);
  });
  Array.from(node.inputs.values()).forEach(function (input) {
    return node.removeInput(input);
  });
  Array.from(node.outputs.values()).forEach(function (output) {
    return node.removeOutput(output);
  });
}
function addIO(node, inputs, outputs) {
  var uniqueInputsCount = new Set(inputs.map(function (i) {
    return i.name;
  })).size;
  var uniqueOutputsCount = new Set(outputs.map(function (i) {
    return i.name;
  })).size;
  if (uniqueInputsCount !== inputs.length) throw "Module ".concat(node.data.module, " has duplicate inputs");
  if (uniqueOutputsCount !== outputs.length) throw "Module ".concat(node.data.module, " has duplicate outputs");
  inputs.forEach(function (i) {
    return node.addInput(new Input(i.name, i.name, i.socket));
  });
  outputs.forEach(function (o) {
    return node.addOutput(new Output(o.name, o.name, o.socket));
  });
}

var Module =
/*#__PURE__*/
function () {
  function Module() {
    _classCallCheck(this, Module);

    this.inputs = {};
    this.outputs = {};
  }

  _createClass(Module, [{
    key: "read",
    value: function read(inputs) {
      this.inputs = inputs;
    }
  }, {
    key: "write",
    value: function write(outputs) {
      var _this = this;

      Object.keys(this.outputs).map(function (key) {
        outputs[key] = _this.outputs[key];
      });
    }
  }, {
    key: "getInput",
    value: function getInput(key) {
      return this.inputs[key];
    }
  }, {
    key: "setOutput",
    value: function setOutput(key, value) {
      this.outputs[key] = value;
    }
  }]);

  return Module;
}();

var ModuleManager =
/*#__PURE__*/
function () {
  function ModuleManager(modules) {
    _classCallCheck(this, ModuleManager);

    this.engine = null;
    this.modules = modules;
    this.inputs = new Map();
    this.outputs = new Map();
  }

  _createClass(ModuleManager, [{
    key: "getInputs",
    value: function getInputs(data) {
      var _this = this;

      return extractNodes(data.nodes, this.inputs).map(function (node) {
        return {
          name: node.data.name,
          socket: _this.socketFactory(node, _this.inputs.get(node.name))
        };
      });
    }
  }, {
    key: "getOutputs",
    value: function getOutputs(data) {
      var _this2 = this;

      return extractNodes(data.nodes, this.outputs).map(function (node) {
        return {
          name: node.data.name,
          socket: _this2.socketFactory(node, _this2.outputs.get(node.name))
        };
      });
    }
  }, {
    key: "socketFactory",
    value: function socketFactory(node, socket) {
      socket = typeof socket === "function" ? socket(node) : socket;
      if (!socket) throw new Error("Socket not found for node with id = ".concat(node.id, " in the module"));
      return socket;
    }
  }, {
    key: "registerInput",
    value: function registerInput(name, socket) {
      this.inputs.set(name, socket);
    }
  }, {
    key: "registerOutput",
    value: function registerOutput(name, socket) {
      this.outputs.set(name, socket);
    }
  }, {
    key: "workerModule",
    value: function () {
      var _workerModule = _asyncToGenerator(
      /*#__PURE__*/
      regeneratorRuntime.mark(function _callee(node, inputs, outputs, args) {
        var data, module, engine;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                if (node.data.module) {
                  _context.next = 2;
                  break;
                }

                return _context.abrupt("return");

              case 2:
                if (this.modules[node.data.module]) {
                  _context.next = 4;
                  break;
                }

                return _context.abrupt("return");

              case 4:
                data = this.modules[node.data.module].data;
                module = new Module();
                engine = this.engine.clone();
                module.read(inputs);
                _context.next = 10;
                return engine.process(data, null, Object.assign({}, args, {
                  module: module,
                  silent: true
                }));

              case 10:
                module.write(outputs);

              case 11:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function workerModule(_x, _x2, _x3, _x4) {
        return _workerModule.apply(this, arguments);
      }

      return workerModule;
    }()
  }, {
    key: "workerInputs",
    value: function workerInputs(node, inputs, outputs) {
      var _ref = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {},
          module = _ref.module;

      if (!module) return;
      outputs['output'] = (module.getInput(node.data.name) || [])[0];
    }
  }, {
    key: "workerOutputs",
    value: function workerOutputs(node, inputs, outputs) {
      var _ref2 = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {},
          module = _ref2.module;

      if (!module) return;
      module.setOutput(node.data.name, inputs['input'][0]);
    }
  }, {
    key: "setEngine",
    value: function setEngine(engine) {
      this.engine = engine;
    }
  }]);

  return ModuleManager;
}();

function install(context, _ref) {
  var engine = _ref.engine,
      modules = _ref.modules;
  var moduleManager = new ModuleManager(modules);
  moduleManager.setEngine(engine);
  context.on('componentregister', function (component) {
    if (!component.module) return; // socket - Rete.Socket instance or function that returns a socket instance

    var _component$module = component.module,
        nodeType = _component$module.nodeType,
        socket = _component$module.socket;
    var name = component.name;

    switch (nodeType) {
      case 'input':
        var inputsWorker = component.worker;
        moduleManager.registerInput(name, socket);

        component.worker = function () {
          for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
            args[_key] = arguments[_key];
          }

          moduleManager.workerInputs.apply(moduleManager, args);
          if (inputsWorker) inputsWorker.apply(component, args);
        };

        break;

      case 'module':
        var builder = component.builder;

        if (builder) {
          component.updateModuleSockets = function (node) {
            removeIO(node, context);
            if (!node.data.module || !modules[node.data.module]) return;
            var data = modules[node.data.module].data;
            var inputs = moduleManager.getInputs(data);
            var outputs = moduleManager.getOutputs(data);

            try {
              addIO(node, inputs, outputs);
            } catch (e) {
              return context.trigger('warn', e);
            }
          };

          component.builder =
          /*#__PURE__*/
          function () {
            var _ref2 = _asyncToGenerator(
            /*#__PURE__*/
            regeneratorRuntime.mark(function _callee(node) {
              return regeneratorRuntime.wrap(function _callee$(_context) {
                while (1) {
                  switch (_context.prev = _context.next) {
                    case 0:
                      component.updateModuleSockets(node);
                      _context.next = 3;
                      return builder.call(component, node);

                    case 3:
                    case "end":
                      return _context.stop();
                  }
                }
              }, _callee);
            }));

            return function (_x) {
              return _ref2.apply(this, arguments);
            };
          }();
        }

        var moduleWorker = component.worker;
        component.worker =
        /*#__PURE__*/
        _asyncToGenerator(
        /*#__PURE__*/
        regeneratorRuntime.mark(function _callee2() {
          var _len2,
              args,
              _key2,
              _args2 = arguments;

          return regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) {
              switch (_context2.prev = _context2.next) {
                case 0:
                  for (_len2 = _args2.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
                    args[_key2] = _args2[_key2];
                  }

                  _context2.next = 3;
                  return moduleManager.workerModule.apply(moduleManager, args);

                case 3:
                  if (moduleWorker) moduleWorker.apply(component, args);

                case 4:
                case "end":
                  return _context2.stop();
              }
            }
          }, _callee2);
        }));
        break;

      case 'output':
        var outputsWorker = component.worker;
        moduleManager.registerOutput(name, socket);

        component.worker = function () {
          for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
            args[_key3] = arguments[_key3];
          }

          if (outputsWorker) outputsWorker.apply(component, args);
          moduleManager.workerOutputs.apply(moduleManager, args);
        };

        break;

      default:
        break;
    }
  });
}

var index = {
  install: install
};

export default index;
//# sourceMappingURL=module-plugin.esm.js.map
