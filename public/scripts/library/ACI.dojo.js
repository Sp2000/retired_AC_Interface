dojo.provide('ACI.dojo');
dojo.require('dojox.data.QueryReadStore');
dojo.addOnLoad(function() {
    dojo.declare('ACI.dojo.QueryStoreModel', dijit.tree.ForestStoreModel, {
        getChildren : function(parentItem, complete_cb, error_cb) {
            if (parentItem.root) {
                return this.inherited(arguments);
            }
            var parentId = this.store.getValue(parentItem, 'id');
            if (!parentId) {
                parentId = 0;
            }
            this.store.fetch( {
                query : {
                    id : parentId
                },
                onComplete : complete_cb,
                onError : error_cb
            });
        },
        mayHaveChildren : function(item) {
            if (item.root)
                return true;

            if (this.store.getValue(item, 'numChildren') > 0)
                return true;

            return false;
        }
    });
    dojo.declare('ACI.dojo.TxTree', dijit.Tree, {
        showRoot : false,
        _clickTarget : null,
        _createTreeNode : function(args) {
            return new ACI.dojo._TxTreeNode(args);
        },
        _onClick : function(evt) {
            console.debug(evt);
            this._clickTarget = evt.target;
            if (this._clickTarget.nodeName == 'A') {
                var nodeWidget = dijit.getEnclosingWidget(this._clickTarget);
                this.onClick(nodeWidget.item, nodeWidget);
                return;
            }
            return this.inherited(arguments);
        }
    });
    dojo.declare('ACI.dojo._TxTreeNode', dijit._TreeNode, {                
        setLabelNode : function(label) {
            if (this.item.root) {
                return this.inherited(arguments);
            }
            var lsid = dojo.doc.createElement('span');
            lsid.className = 'lsid';
            lsid.appendChild(dojo.doc.createTextNode(
                this.tree.model.store.getValue(this.item, 'lsid')));
            if (this.tree.model.store
                    .getValue(this.item, 'url') == null) {                
                var span = dojo.doc.createElement('span');
                span.appendChild(dojo.doc
                        .createTextNode(this.tree.model.store.getValue(
                                this.item, 'type')));
                this.labelNode.appendChild(span);
                this.labelNode.appendChild(dojo.doc
                        .createTextNode(' ' + label));
                this.labelNode.appendChild(lsid);
            } else {
                var a = dojo.doc.createElement('a');
                a.href = this.tree.model.store.getValue(this.item, 'url');
                a.appendChild(dojo.doc.createTextNode(label));
                var subsp = this.tree.model.store.getValue(this.item, 'subsp');
                if(subsp != null) {
                    var span = dojo.doc.createElement('span');
                    span.className = 'subsp';
                    span.appendChild(dojo.doc.createTextNode(' subsp. '));
                    a.appendChild(span);
                    a.appendChild(dojo.doc.createTextNode(subsp));
                }
                this.labelNode.innerHTML = '';                
                dojo.place(lsid, this.expandoNode, 'after');
                dojo.place(a, this.expandoNode, 'after');               
                this.expandoNodeText.parentNode
                        .removeChild(this.expandoNodeText);
            }
        }            
    });
});