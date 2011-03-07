dojo.provide('ACI.dojo.TxTreeNode');
dojo.declare('ACI.dojo.TxTreeNode', dijit._TreeNode, {                
    setLabelNode : function(label) {
        if (this.item.root) {
            return this.inherited(arguments);
        }
        var type = this.tree.model.store.getValue(this.item, 'type');
        var bullet = dojo.doc.createElement('span');
        bullet.className = 'bullet';
        bullet.appendChild(
        		dojo.doc.createTextNode(
        		' - '
        	)
        );
        
        var statistics = dojo.doc.createElement('span');
        statistics.className = 'treeStatistics';
        var temp = dojo.clone(bullet);
        statistics.appendChild(temp);
        statistics.appendChild(
        	dojo.doc.createTextNode(
        		this.tree.model.store.getValue(this.item, 'total') + ' spp;' +
        		' est ' + this.tree.model.store.getValue(this.item, 'estimation') + ';' +
        		' ' + this.tree.model.store.getValue(this.item, 'percentage') + '%'
        	)
        );
        
        var source_databases = this.tree.model.store.getValue(this.item, 'source_databases');
        var gsdCounter = 0;
    	var source_database = dojo.doc.createElement('span');
    	source_database.className = 'treeSourceDatabase';
        var temp = dojo.clone(bullet);
    	source_database.appendChild(temp);
    	separator = ',';
    	for(var i in source_databases)
        {
            var a = dojo.doc.createElement('a');
            a.href = baseUrl + '/details/database/id/' + source_databases[i].source_database_id;
            a.title = source_databases[i].full_name;
            a.appendChild(dojo.doc.createTextNode(source_databases[i].short_name));
    		if(gsdCounter > 0) {
    			source_database.appendChild(dojo.doc.createTextNode(separator));
    		}
        	source_database.appendChild(a);
            gsdCounter++;
        }
        if(gsdCounter > 5) {
        	source_database = dojo.doc.createElement('span');
        	source_database.appendChild(temp);
        	source_database.appendChild(dojo.doc.createTextNode('multiple GSD\'s'));
        }
        
        if (this.tree.model.store
                .getValue(this.item, 'url') == null) {  
            var rank = dojo.doc.createElement('span');            
            rank.className = 'rank';
            rank.id = 'sn-' + this.tree.model.store.getValue(this.item, 'id');
            rank.appendChild(dojo.doc
                    .createTextNode(type));
            this.labelNode.appendChild(rank);
            var taxon = dojo.doc.createElement('span');
            taxon.className = 'nodeLabel node-' + type;
            taxon.appendChild(dojo.doc
                    .createTextNode(' ' + label));
            taxon.appendChild(statistics);
            taxon.appendChild(source_database);
            this.labelNode.appendChild(taxon);
        } else {
            var leaf = dojo.doc.createElement('span');
            leaf.className = 'leaf';
            leaf.id = 'sn-' + this.tree.model.store.getValue(this.item, 'id');
            var a = dojo.doc.createElement('a');
            a.href = this.tree.model.store.getValue(this.item, 'url');
            if(type == 'Infraspecies') {
                for(var i in label) {
                    if(i > 0) {
                        a.appendChild(dojo.doc.createTextNode(' '));
                    }
                    // is a marker
                    if(label[i][1] == true) {
                        var span = dojo.doc.createElement('span');
                        span.className = 'marker';
                        span.appendChild(dojo.doc.createTextNode(label[i][0]));
                        a.appendChild(span);
                    }
                    else {
                        a.appendChild(dojo.doc.createTextNode(label[i][0]));
                    }
                }
            }
            else {
                a.appendChild(dojo.doc.createTextNode(label));
            }            
            leaf.appendChild(a);
            this.labelNode.innerHTML = '';
            leaf.appendChild(source_database);
            this.expandoNode.parentNode.className += ' dijitTreeLeafLabel'
            this.labelNode.appendChild(leaf);
        }
    },    
    expand : function() {
        this.inherited(arguments);
        if(!hierarchy.length) {
            return;
        }
        var tree = this.tree;
        var pos = null;
        dojo.forEach(this.getChildren(), function(node, index, array) {
            pos = dojo.indexOf(hierarchy, node.item.i.id);
            // in array
            if(pos >= 0) {
                tree._expandNode(node);
                // latest element
                if(pos == hierarchy.length - 1) {
                    tree.focusNode(node);
                    setTimeout("scrollToEl(\"" + node.labelNode.children[0].id + "\")", 100);
                }
                else {
                    scrollToEl(node.labelNode.children[0].id);
                }
                hierarchy.unshift();                
                return;
            }
        });
    }
});