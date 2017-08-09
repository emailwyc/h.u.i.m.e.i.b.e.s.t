# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Department
from ...forms.admin.department import CreateForm, UpdateForm
from flask import render_template, redirect, url_for, flash

admin_department = Blueprint('admin_department', __name__, url_prefix='/admin/department')


@admin_department.route('/list', methods=['GET'])
@http_auth_required
def department_list():
    departments = Department.objects().order_by('-tags', '-parent', '+order', '+id')
    if departments:
        parent = {}
        [parent.update({item.id: item.name}) for item in departments if item.parent is None]
        for item in departments:
            if item.parent is not None:
                item.parent = parent.get(item.parent)
            else:
                item.parent = '-'
    return render_template('admin/department/list.html', departments=departments)


@admin_department.route('/show/<id>', methods=['GET'])
@http_auth_required
def department_show(id):
    department = Department.objects(id=id).first_or_404()
    if department.parent is not None:
        parent = Department.objects(id=department.parent).first_or_404()
        department.parent = parent.name
    else:
        department.parent = '-'
    return render_template('admin/department/show.html', department=department)


@admin_department.route('/create', methods=['GET', 'POST'])
@http_auth_required
def department_create():
    form = CreateForm()
    form_init(form)
    try:
        if form.validate_on_submit():
            department = department_info(form)
            flash('科室 {0} 添加成功'.format(form.data.get('name')))
            return redirect('/admin/department/list')
    except Exception as e:
        print(e)

    return render_template('admin/department/form.html', create=True, form=form)


@admin_department.route('/update/<id>', methods=['GET', 'POST'])
@http_auth_required
def department_update(id):
    form = None
    try:
        form = UpdateForm()
        form_init(form)
        if request.method == 'POST' and form.validate_on_submit():
            department = department_info(form, id)
            flash('科室 {0} 修改成功'.format(form.data.get('name')))
            return redirect('/admin/department/list')
    except Exception as e:
        print(e)

    if request.method == 'GET':
        department = Department.objects(id=id).first_or_404()
        if department:
            form = UpdateForm(obj=department)
            form_init(form)

    return render_template('admin/department/form.html', create=False, form=form)


def department_info(form, id=None):
    name = form.data.get('name')
    if id is None and Department.objects(name=name):
        form.name.errors.append('科室已经存在')
        raise ResourceWarning('科室已经存在')
    if id:
        if Department.objects(id__ne=id, name=name):
            form.name.errors.append('科室已经存在')
            raise ResourceWarning('科室已经存在')
        department = Department.objects(id=id).first()
        if department is None:
            form.name.errors.append('医院不存在')
            raise ResourceWarning('医院不存在')
    else:
        department = Department()

    for k, v in form.data.items():
        if k == 'parent':
            v = _parent(form, department, v)
        setattr(department, k, v)

    department.updated_at = datetime.utcnow()
    department.save()

    return department


def _parent(form, department, parent):
    if not parent:
        return None
    try:
        if department.id and Department.objects(parent=department.id):
            form.parent.errors.append('当前科室包含二级科室，不能成为其它科室的二级科室')
            raise ResourceWarning('当前科室包含二级科室，不能成为其它科室的二级科室')
        from bson import ObjectId
        parent = ObjectId(parent)
        if parent == department.id:
            form.parent.errors.append('上级科室不能是自己')
            raise ResourceWarning('上级科室不能是自己')
        return parent 
    except Exception as e:
        form.parent.errors.append('上级科室错误')
        raise ResourceWarning('上级科室错误')


def form_init(form):
    department_ids = Department.objects(parent__exists=False).order_by('-id')
    choices = [('', '无')]
    if department_ids:
        for x in department_ids:
            choices.append((str(x.id), x.name))
    form.parent.choices = choices


# vim:ts=4:sw=4
