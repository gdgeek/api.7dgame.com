/*!
* rete-area-plugin v0.2.1 
* (c) 2019  
* Released under the ISC license.
*/
function ___$insertStyle(css) {
  if (!css) {
    return;
  }
  if (typeof window === 'undefined') {
    return;
  }

  var style = document.createElement('style');

  style.setAttribute('type', 'text/css');
  style.innerHTML = css;
  document.head.appendChild(style);
  return css;
}

___$insertStyle(".rete-background {\n  display: table;\n  z-index: -1;\n  position: absolute;\n  top: -320000px;\n  left: -320000px;\n  width: 640000px;\n  height: 640000px;\n}\n.rete-background.default {\n  background-size: 32px 32px;\n  background-image: linear-gradient(to right, #ccc 1px, transparent 1px), linear-gradient(to bottom, #ccc 1px, transparent 1px);\n}");

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

function _slicedToArray(arr, i) {
  return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest();
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

function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}

function _iterableToArray(iter) {
  if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
}

function _iterableToArrayLimit(arr, i) {
  var _arr = [];
  var _n = true;
  var _d = false;
  var _e = undefined;

  try {
    for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
      _arr.push(_s.value);

      if (i && _arr.length === i) break;
    }
  } catch (err) {
    _d = true;
    _e = err;
  } finally {
    try {
      if (!_n && _i["return"] != null) _i["return"]();
    } finally {
      if (_d) throw _e;
    }
  }

  return _arr;
}

function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance");
}

function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance");
}

var Background = function Background(editor, element) {
  _classCallCheck(this, Background);

  if (!element) return;
  var el = element instanceof HTMLElement ? element : document.createElement('div');
  el.classList += " rete-background ".concat(element === true ? 'default' : '');
  editor.view.area.appendChild(el);
};

var Restrictor =
/*#__PURE__*/
function () {
  function Restrictor(editor, scaleExtent, translateExtent) {
    _classCallCheck(this, Restrictor);

    this.editor = editor;
    this.scaleExtent = scaleExtent;
    this.translateExtent = translateExtent;
    if (scaleExtent) editor.on('zoom', this.restrictZoom.bind(this));
    if (translateExtent) editor.on('translate', this.restrictTranslate.bind(this));
  }

  _createClass(Restrictor, [{
    key: "restrictZoom",
    value: function restrictZoom(data) {
      var se = typeof this.scaleExtent === 'boolean' ? {
        min: 0.1,
        max: 1
      } : this.scaleExtent;
      var tr = data.transform;
      if (data.zoom < se.min) data.zoom = se.min;else if (data.zoom > se.max) data.zoom = se.max;
    }
  }, {
    key: "restrictTranslate",
    value: function restrictTranslate(data) {
      var te = typeof this.translateExtent === 'boolean' ? {
        width: 5000,
        height: 4000
      } : this.translateExtent;
      var container = this.editor.view.container;
      var k = data.transform.k;
      var kw = te.width * k,
          kh = te.height * k;
      var cx = container.clientWidth / 2;
      var cy = container.clientHeight / 2;
      data.x -= cx;
      data.y -= cy;
      if (data.x > kw) data.x = kw;else if (data.x < -kw) data.x = -kw;
      if (data.y > kh) data.y = kh;else if (data.y < -kh) data.y = -kh;
      data.x += cx;
      data.y += cy;
    }
  }]);

  return Restrictor;
}();

var SnapGrid =
/*#__PURE__*/
function () {
  function SnapGrid(editor, _ref) {
    var _this = this;

    var _ref$size = _ref.size,
        size = _ref$size === void 0 ? 16 : _ref$size,
        _ref$dynamic = _ref.dynamic,
        dynamic = _ref$dynamic === void 0 ? true : _ref$dynamic;

    _classCallCheck(this, SnapGrid);

    this.editor = editor;
    this.size = size;
    if (dynamic) this.editor.on('nodetranslate', this.onTranslate.bind(this));else this.editor.on('rendernode', function (_ref2) {
      var node = _ref2.node,
          el = _ref2.el;
      el.addEventListener('mouseup', _this.onDrag.bind(_this, node));
      el.addEventListener('touchend', _this.onDrag.bind(_this, node));
      el.addEventListener('touchcancel', _this.onDrag.bind(_this, node));
    });
  }

  _createClass(SnapGrid, [{
    key: "onTranslate",
    value: function onTranslate(data) {
      var x = data.x,
          y = data.y;
      data.x = this.snap(x);
      data.y = this.snap(y);
    }
  }, {
    key: "onDrag",
    value: function onDrag(node) {
      var _node$position = _slicedToArray(node.position, 2),
          x = _node$position[0],
          y = _node$position[1];

      node.position[0] = this.snap(x);
      node.position[1] = this.snap(y);
      console.log(this, x, y, node.position);
      this.editor.view.nodes.get(node).update();
      this.editor.view.updateConnections({
        node: node
      });
    }
  }, {
    key: "snap",
    value: function snap(value) {
      return Math.round(value / this.size) * this.size;
    }
  }]);

  return SnapGrid;
}();

var min = function min(arr) {
  return arr.length === 0 ? 0 : Math.min.apply(Math, _toConsumableArray(arr));
};

var max = function max(arr) {
  return arr.length === 0 ? 0 : Math.max.apply(Math, _toConsumableArray(arr));
};

function nodesBBox(editor, nodes) {
  var left = min(nodes.map(function (node) {
    return node.position[0];
  }));
  var top = min(nodes.map(function (node) {
    return node.position[1];
  }));
  var right = max(nodes.map(function (node) {
    return node.position[0] + editor.view.nodes.get(node).el.clientWidth;
  }));
  var bottom = max(nodes.map(function (node) {
    return node.position[1] + editor.view.nodes.get(node).el.clientHeight;
  }));
  return {
    left: left,
    right: right,
    top: top,
    bottom: bottom,
    width: Math.abs(left - right),
    height: Math.abs(top - bottom),
    getCenter: function getCenter() {
      return [(left + right) / 2, (top + bottom) / 2];
    }
  };
}

function zoomAt(editor) {
  var nodes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : editor.nodes;
  var bbox = nodesBBox(editor, nodes);

  var _bbox$getCenter = bbox.getCenter(),
      _bbox$getCenter2 = _slicedToArray(_bbox$getCenter, 2),
      x = _bbox$getCenter2[0],
      y = _bbox$getCenter2[1];

  var _ref = [editor.view.container.clientWidth, editor.view.container.clientHeight],
      w = _ref[0],
      h = _ref[1];
  var area = editor.view.area;
  var kw = w / bbox.width,
      kh = h / bbox.height;
  var k = Math.min(kh * 0.9, kw * 0.9, 1);
  area.transform.x = area.container.clientWidth / 2 - x * k;
  area.transform.y = area.container.clientHeight / 2 - y * k;
  area.zoom(k, 0, 0);
  area.update();
}

function install(editor, params) {
  var background = params.background || false;
  var snap = params.snap || false;
  var scaleExtent = params.scaleExtent || false;
  var translateExtent = params.translateExtent || false;

  if (background) {
    this._background = new Background(editor, background);
  }

  if (scaleExtent || translateExtent) {
    this._restrictor = new Restrictor(editor, scaleExtent, translateExtent);
  }

  if (snap) {
    this._snap = new SnapGrid(editor, snap);
  }
}

var index = {
  install: install,
  zoomAt: zoomAt
};

export default index;
//# sourceMappingURL=area-plugin.esm.js.map
