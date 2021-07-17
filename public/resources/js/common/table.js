/**
 * 获取表单数据
 * @param {*} filter lay-filter属性值
 * @param {*} itemForm dom对象
 */
function getFormData(filter, itemForm) {
    itemForm = itemForm || $('.layui-form[lay-filter="' + filter + '"]').eq(0)

    var nameIndex = {} //数组 name 索引
        , field = {}
        , fieldElem = itemForm.find('input,select,textarea') //获取所有表单域

    layui.each(fieldElem, function (_, item) {
        item.name = (item.name || '').replace(/^\s*|\s*&/, '')

        if (!item.name) return

        //用于支持数组 name
        if (/^.*\[\]$/.test(item.name)) {
            var key = item.name.match(/^(.*)\[\]$/g)[0]
            nameIndex[key] = nameIndex[key] | 0
            item.name = item.name.replace(/^(.*)\[\]$/, '$1[' + (nameIndex[key]++) + ']')
        }

        if (/^checkbox|radio$/.test(item.type) && !item.checked) return
        field[item.name] = item.value
    })

    return field
};
