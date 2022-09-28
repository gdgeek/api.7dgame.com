var MrPP = {};

MrPP.Node = {};
MrPP.Node.install = function (editor, options) {
    editor.on("componentregister", component => {
        if (typeof component.setNodes !== "undefined") {
            component.setNodes(options["nodes"]);
        }
    });
};

MrPP.DB = {};
MrPP.DB.data = null;
MrPP.DB.refresh;

MrPP.DB.install = function (editor, options) {
    MrPP.DB.options = options;
    $(window).unload(function () {
        MrPP.DB.auto();
    });

    editor.on("process connectioncreate connectionremove nodecreate noderemove", (e) => {
        MrPP.DB.auto();
    });

    editor.on("process", (e) => {
      //  console.log("process");
    });

    editor.on("connectioncreate", (e) => {
     //   console.log("connectioncreate");
    });

    editor.on("connectionremove", (e) => {
     //   console.log("connectionremove");
    });

    editor.on("nodecreate", (e) => {
       // console.log("nodecreate");
    });

    editor.on("noderemove", (e) => {
      //  console.log("noderemove");
    });

    editor.on("componentregister", component => {
        if (typeof component.export !== "undefined") {
            component.export = function () {
                MrPP.DB.auto();
            };
        }
    });
    //MrPP.DB.auto();
};

MrPP.DB.auto = function () {
    if (!MrPP.DB.save()) {
        setTimeout("MrPP.DB.save();", 1000);
    }
}

MrPP.DB.save = function () {
    if (MrPP.editor.silent) {
        return false;
    }

    
    var data = JSON.stringify(MrPP.editor.toJSON());
    if (MrPP.DB.on_saving) {
        MrPP.DB.on_saving();
    }
    console.log(data)
    if (data != MrPP.DB.data) {
        console.log('!!!!!!!!')
        MrPP.DB.data = data;
        $.post({
            url: MrPP.DB.options["url"],
            data: {
                "json": MrPP.DB.data,
                "serialization": "",
                "options": MrPP.DB.options
            },
            success: function (result) {
                console.log("db success");
                if (MrPP.DB.on_saved) {
                    MrPP.DB.on_saved();
                }
            }
        }).fail(function (result,result1,result2) {
            console.log(result);
            console.log(result1);
            console.log(result2);
        });
        return true;
    } else {
        MrPP.DB.on_saved();
        return false;
    }
};

MrPP.Internal = {};

MrPP.Internal.install = function (editor, options) {
    editor.on("noderemoved", node => {
        $.post({
            url: options["removed_url"],
            data: {
                "node_id": node.id,
                "project_id": options["project_id"]
            },
            success: function (result) {
                console.log("delete success" + result);
                MrPP.DB.save();
            }
        });
    });
    editor.on("componentregister", component => {
        if (typeof component.push !== "undefined") {
            component.push = function (data) {
                $(location).attr("href", options['url'] + "&" + "node_id=" + data.node_id + "&template=" + data.template);

            };
        }
    });
};

MrPP.Locked = {};

MrPP.Locked.install = function (editor, options) {
    var components = options['components'];
    if (!components) {
        return;
    }

    editor.on("noderemove", component => {
        var locked = {};
        editor.nodes.forEach(function (e) {
            if (typeof components[e.name] !== "undefined") {
                if (typeof locked[e.name] === "undefined") {
                    locked[e.name] = 0;
                }
                locked[e.name] = locked[e.name] + 1;
            }
        });
        if (typeof components[component.name] !== "undefined" && components[component.name][0] >= locked[component.name]) {
            alert('禁止删除' + component.name + '模块(至少' + components[component.name][1] + '个)');
            return false;
        }
        return true;
    });

    editor.on("nodecreate", component => {
        var locked = {};
        editor.nodes.forEach(function (e) {
            if (typeof components[e.name] !== "undefined") {
                if (typeof locked[e.name] === "undefined") {
                    locked[e.name] = 0;
                }
                locked[e.name] = locked[e.name] + 1;
            }
        });
        if (typeof components[component.name] !== 'undefined' && components[component.name][1] >= locked[component.name]) {
            alert('无法添加' + component.name + '模块(至多' + components[component.name][1] + '个)');
            return false;
        }
        return true;
    });
};

MrPP.Polygens = {};
MrPP.Polygens.install = function (editor, options) {
    editor.on('componentregister', component => {
        if (typeof component.setPolygens !== "undefined") {
            component.setPolygens(options['polygens']);
           // alert(JSON.stringify(options['polygens']));
        }

    });
};

MrPP.Sounds = {};
MrPP.Sounds.install = function (editor, options) {

    editor.on("componentregister", component => {
        if (typeof component.setSounds !== "undefined") {
            component.setSounds(options["sounds"]);
        }
    });
}
MrPP.Videos = {};
MrPP.Videos.install = function (editor, options) {

    editor.on("componentregister", component => {
        if (typeof component.setVideos !== "undefined") {
            component.setVideos(options["videos"]);
        }
        /*
        if (typeof component.getVideoData !== "undefined") {
            component.getVideoData = function (video) {
                var name = "/" + video.file_name;
                return {
                    "name": name,
                    "url": options["url"],
                    "cache": true,
                };
            };
        }*/
    });
};

MrPP.Pictures = {};
MrPP.Pictures.install = function (editor, options) {
    editor.on("componentregister", component => {
        if (typeof component.setPictures !== "undefined") {
            component.setPictures(options["pictures"]);
        }
        /*
        if (typeof component.getPictureData !== "undefined") {
            component.getPictureData = function (picture) {
                var name = "/" + picture.file_name;
                return {
                    "name": name,
                    "url": options['url'],
                    "cache": true,
                };
            };
        }*/
    });
};