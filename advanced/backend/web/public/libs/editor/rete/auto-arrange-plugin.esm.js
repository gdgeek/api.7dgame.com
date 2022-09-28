/*!
* rete-auto-arrange-plugin v0.4.0 
* (c) 2019 Vitaliy Stoliarov 
* Released under the MIT license.
*/
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

function _toConsumableArray(arr) {
  return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread();
}

function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  }
}

function _iterableToArray(iter) {
  if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
}

function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance");
}

var Board =
/*#__PURE__*/
function () {
  function Board() {
    _classCallCheck(this, Board);

    this._cols = [];
  }

  _createClass(Board, [{
    key: "add",
    value: function add(columnIndex, value) {
      if (!this._cols[columnIndex]) this._cols[columnIndex] = [];

      this._cols[columnIndex].push(value);
    }
  }, {
    key: "toArray",
    value: function toArray() {
      var _this = this;

      var normalized = Object.keys(this._cols).sort(function (i1, i2) {
        return +i1 - +i2;
      }).map(function (key) {
        return _this._cols[key];
      });
      return normalized;
    }
  }]);

  return Board;
}();

var Cache =
/*#__PURE__*/
function () {
  function Cache() {
    _classCallCheck(this, Cache);

    this._map = new WeakMap();
  }

  _createClass(Cache, [{
    key: "track",
    value: function track(value) {
      if (this._map.has(value)) return true;

      this._map.set(value, true);
    }
  }]);

  return Cache;
}();

var AutoArrange =
/*#__PURE__*/
function () {
  function AutoArrange(editor, margin, depth, vertical) {
    _classCallCheck(this, AutoArrange);

    this.editor = editor;
    this.margin = margin;
    this.depth = depth;
    this.vertical = vertical;
  }

  _createClass(AutoArrange, [{
    key: "getNodes",
    value: function getNodes(node) {
      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'output';
      var nodes = [];
      var key = "".concat(type, "s");
      var _iteratorNormalCompletion = true;
      var _didIteratorError = false;
      var _iteratorError = undefined;

      try {
        for (var _iterator = node[key].values()[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
          var io = _step.value;
          var _iteratorNormalCompletion2 = true;
          var _didIteratorError2 = false;
          var _iteratorError2 = undefined;

          try {
            for (var _iterator2 = io.connections.values()[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
              var connection = _step2.value;
              nodes.push(connection[type === 'input' ? 'output' : 'input'].node);
            }
          } catch (err) {
            _didIteratorError2 = true;
            _iteratorError2 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion2 && _iterator2["return"] != null) {
                _iterator2["return"]();
              }
            } finally {
              if (_didIteratorError2) {
                throw _iteratorError2;
              }
            }
          }
        }
      } catch (err) {
        _didIteratorError = true;
        _iteratorError = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion && _iterator["return"] != null) {
            _iterator["return"]();
          }
        } finally {
          if (_didIteratorError) {
            throw _iteratorError;
          }
        }
      }

      return nodes;
    }
  }, {
    key: "getNodesBoard",
    value: function getNodesBoard(node) {
      var _this = this;

      var cache = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : new Cache();
      var board = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : new Board();
      var depth = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
      if (this.depth && depth > this.depth) return;
      if (cache.track(node)) return;
      board.add(depth, node);
      this.getNodes(node, 'output').map(function (n) {
        return _this.getNodesBoard(n, cache, board, depth + 1);
      });
      this.getNodes(node, 'input').map(function (n) {
        return _this.getNodesBoard(n, cache, board, depth - 1);
      });
      return board;
    }
  }, {
    key: "getNodeSize",
    value: function getNodeSize(node) {
      var el = this.editor.view.nodes.get(node).el;
      return this.vertical ? {
        height: el.clientWidth,
        width: el.clientHeight
      } : {
        width: el.clientWidth,
        height: el.clientHeight
      };
    }
  }, {
    key: "translateNode",
    value: function translateNode(node, _ref) {
      var _this$editor$view$nod;

      var x = _ref.x,
          y = _ref.y;
      var position = this.vertical ? [y, x] : [x, y];

      (_this$editor$view$nod = this.editor.view.nodes.get(node)).translate.apply(_this$editor$view$nod, position);

      this.editor.view.updateConnections({
        node: node
      });
    }
  }, {
    key: "arrange",
    value: function arrange() {
      var _this2 = this;

      var node = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.editor.nodes[0];
      var board = this.getNodesBoard(node).toArray();
      var margin = this.vertical ? {
        x: this.margin.y,
        y: this.margin.x
      } : this.margin;
      var x = 0;
      var _iteratorNormalCompletion3 = true;
      var _didIteratorError3 = false;
      var _iteratorError3 = undefined;

      try {
        for (var _iterator3 = board[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
          var column = _step3.value;
          var sizes = column.map(function (node) {
            return _this2.getNodeSize(node);
          });
          var columnWidth = Math.max.apply(Math, _toConsumableArray(sizes.map(function (size) {
            return size.width;
          })));
          var fullHeight = sizes.reduce(function (sum, node) {
            return sum + node.height + margin.y;
          }, 0);
          var y = 0;
          var _iteratorNormalCompletion4 = true;
          var _didIteratorError4 = false;
          var _iteratorError4 = undefined;

          try {
            for (var _iterator4 = column[Symbol.iterator](), _step4; !(_iteratorNormalCompletion4 = (_step4 = _iterator4.next()).done); _iteratorNormalCompletion4 = true) {
              var _node = _step4.value;
              var position = {
                x: x,
                y: y - fullHeight / 2
              };

              var _this$getNodeSize = this.getNodeSize(_node),
                  height = _this$getNodeSize.height;

              this.translateNode(_node, position);
              y += height + margin.y;
            }
          } catch (err) {
            _didIteratorError4 = true;
            _iteratorError4 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion4 && _iterator4["return"] != null) {
                _iterator4["return"]();
              }
            } finally {
              if (_didIteratorError4) {
                throw _iteratorError4;
              }
            }
          }

          x += columnWidth + margin.x;
        }
      } catch (err) {
        _didIteratorError3 = true;
        _iteratorError3 = err;
      } finally {
        try {
          if (!_iteratorNormalCompletion3 && _iterator3["return"] != null) {
            _iterator3["return"]();
          }
        } finally {
          if (_didIteratorError3) {
            throw _iteratorError3;
          }
        }
      }
    }
  }]);

  return AutoArrange;
}();

function install(editor, _ref) {
  var _ref$margin = _ref.margin,
      margin = _ref$margin === void 0 ? {
    x: 50,
    y: 50
  } : _ref$margin,
      _ref$depth = _ref.depth,
      depth = _ref$depth === void 0 ? null : _ref$depth,
      _ref$vertical = _ref.vertical,
      vertical = _ref$vertical === void 0 ? false : _ref$vertical;
  editor.bind('arrange');
  var ar = new AutoArrange(editor, margin, depth, vertical);
  editor.on('arrange', function (_ref2) {
    var node = _ref2.node;
    return ar.arrange(node);
  });

  editor.arrange = function (node) {
    console.log("Deprecated: use editor.trigger('arrange', { node }) instead");
    ar.arrange(node);
  };
}

var index = {
  name: 'auto-arrange',
  install: install
};

export default index;
//# sourceMappingURL=auto-arrange-plugin.esm.js.map
