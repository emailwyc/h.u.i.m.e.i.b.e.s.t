# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Region
from ...forms.admin.region import CreateForm, UpdateForm
from flask import render_template, redirect, url_for, flash

admin_region = Blueprint('admin_region', __name__, url_prefix='/admin/region')


@admin_region.route('/list', methods=['GET'])
@http_auth_required
def region_list():
    regions = Region.objects().order_by('-parent', '+weight', '+id')
    if regions:
        parent = {}
        [parent.update({item.id: item.name}) for item in regions if item.parent is None]
        for item in regions:
            if item.parent is not None:
                item.parent = parent.get(item.parent)
            else:
                item.parent = '-'
    return render_template('admin/region/list.html', regions=regions)


@admin_region.route('/show/<id>', methods=['GET'])
@http_auth_required
def region_show(id):
    region = Region.objects(id=id).first_or_404()
    if region.parent is not None:
        parent = Region.objects(id=region.parent).first_or_404()
        region.parent = parent.name
    else:
        region.parent = '-'
    return render_template('admin/region/show.html', region=region)


@admin_region.route('/create', methods=['GET', 'POST'])
@http_auth_required
def region_create():
    form = CreateForm()
    form_init(form)
    try:
        if form.validate_on_submit():
            region = region_info(form)
            flash('区域 {0} 添加成功'.format(form.data.get('name')))
            return redirect('/admin/region/list')
    except Exception as e:
        print(e)

    return render_template('admin/region/form.html', create=True, form=form)


@admin_region.route('/update/<id>', methods=['GET', 'POST'])
@http_auth_required
def region_update(id):
    form = None
    try:
        form = UpdateForm()
        form_init(form)
        if request.method == 'POST' and form.validate_on_submit():
            region = region_info(form, id)
            flash('区域 {0} 修改成功'.format(form.data.get('name')))
            return redirect('/admin/region/list')
    except Exception as e:
        print(e)

    if request.method == 'GET':
        region = Region.objects(id=id).first_or_404()
        if region:
            form = UpdateForm(obj=region)
            form_init(form)

    return render_template('admin/region/form.html', create=False, form=form)


def region_info(form, id=None):
    name = form.data.get('name')
    if id is None and Region.objects(name=name):
        form.name.errors.append('区域已经存在')
        raise ResourceWarning('区域已经存在')
    if id:
        if Region.objects(id__ne=id, name=name):
            form.name.errors.append('区域已经存在')
            raise ResourceWarning('区域已经存在')
        region = Region.objects(id=id).first()
        if region is None:
            form.name.errors.append('区域不存在')
            raise ResourceWarning('区域不存在')
    else:
        region = Region()

    for k, v in form.data.items():
        if k == 'parent':
            v = _parent(form, region, v)
            if v:
                region.level=2
            
        setattr(region, k, v)

    region.save()

    return region


def _parent(form, region, parent):
    if not parent:
        return None
    try:
        if region.id and Region.objects(parent=region.id):
            form.parent.errors.append('当前区域包含二级区域，不能成为其它区域的二级区域')
            raise ResourceWarning('当前区域包含二级区域，不能成为其它区域的二级区域')
        from bson import ObjectId
        parent = ObjectId(parent)
        if parent == region.id:
            form.parent.errors.append('上级区域不能是自己')
            raise ResourceWarning('上级区域不能是自己')
        return parent 
    except Exception as e:
        form.parent.errors.append('上级区域错误')
        raise ResourceWarning('上级区域错误')


def form_init(form):
    region_ids = Region.objects(parent__exists=False).order_by('-id')
    choices = [('', '无')]
    if region_ids:
        for x in region_ids:
            choices.append((str(x.id), x.name))
        form.parent.choices = choices


# vim:ts=4:sw=4
