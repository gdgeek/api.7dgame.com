

Blockly.Lua["action_block"] = function(block) {
  var text_action = block.getFieldValue("ACTION");
  var statements_action = Blockly.Lua.statementToCode(block, "ACTION");
  // TODO: Assemble Lua into code variable.
  var code = "if '"+text_action+"' == key then\\n"+statements_action+" \\nend ";
  return code;
};





Blockly.Blocks["action_block"] ={
  init: function() {
    this.appendDummyInput()
        .setAlign(Blockly.ALIGN_RIGHT)
        .appendField("����")
        .appendField(new Blockly.FieldDropdown([$action]), "NAME");
    this.appendStatementInput("NAME")
        .setCheck("Task");
    this.setColour(230);
 this.setTooltip("��ѡ����Ӧ����");
 this.setHelpUrl("http://MrPP.com");
  }
};
