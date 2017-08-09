# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Hospital,Region
from ...forms.admin.hospital import CreateForm, UpdateForm
from flask import render_template, redirect, url_for, flash
import hashlib


admin_hospital = Blueprint('admin_hospital', __name__, url_prefix='/admin/hospital')


@admin_hospital.route('/list', methods=['GET'])
@http_auth_required
def hospital_list():
    hospitals = Hospital.objects(status=1).order_by('-order')
    for hospital in hospitals:
        if hospital.region_id:
            region = Region.objects(id=hospital.region_id).first()
            if region:
                hospital.region_id = region.name
        if hospital.region_child_id:
            region_child = Region.objects(id=hospital.region_child_id).first()
            if region:
                hospital.region_child_id = region_child.name
        if hospital.branches and isinstance(hospital.branches, list):
            hospital.branches = '<br><br>'.join(['{0}<br>{1}'.format(branch.get('name'), branch.get('address')) for branch in hospital.branches])
        else:
            hospital.branches = ''

    return render_template('admin/hospital/list.html', hospitals=hospitals)


@admin_hospital.route('/show/<id>', methods=['GET'])
@http_auth_required
def hospital_show(id):
    hospital = Hospital.objects(id=id).first_or_404()
    if hospital.region_id:
        region = Region.objects(id=hospital.region_id).first()
        if region:
            hospital.region_id = region.name
    if hospital.region_child_id:
        region = Region.objects(id=hospital.region_child_id).first()
        if region:
            hospital.region_child_id = region.name
    if hospital.branches and isinstance(hospital.branches, list):
        hospital.branches = '<br><br>'.join(['{0}<br>{1}'.format(branch.get('name'), branch.get('address')) for branch in hospital.branches])
    else:
        hospital.branches = ''

    return render_template('admin/hospital/show.html', hospital=hospital)


@admin_hospital.route('/create', methods=['GET', 'POST'])
@http_auth_required
def hospital_create():
    form = CreateForm()
    form_init(form)

    try:
        if form.validate_on_submit():
            hospital = hospital_info(form)
            flash('医院 {0} 添加成功'.format(form.data.get('name')))
            return redirect('/admin/hospital/list')
    except Exception as e:
        print(e)

    branches_decode(form)
    return render_template('admin/hospital/form.html', create=True, form=form)


@admin_hospital.route('/update/<id>', methods=['GET', 'POST'])
@http_auth_required
def hospital_update(id):
    form = None
    try:
        form = UpdateForm()
        form_init(form)

        if request.method == 'POST' and form.validate_on_submit():
            hospital = hospital_info(form, id)
            flash('医院 {0} 修改成功'.format(form.data.get('name')))
            return redirect('/admin/hospital/list')
    except Exception as e:
        print(e)

    if request.method == 'GET':
        hospital = Hospital.objects(id=id).first_or_404()
        if hospital:
            form = UpdateForm(obj=hospital)
            form_init(form)

    branches_decode(form)
    return render_template('admin/hospital/form.html', create=False, form=form)


def hospital_info(form, id=None):
    name = form.data.get('name')
    if id is None and Hospital.objects(name=name):
        form.name.errors.append('医院已经存在')
        raise ResourceWarning('医院已经存在')
    if id:
        hospital = Hospital.objects(id=id).first()
        if hospital is None:
            form.name.errors.append('医院不存在')
            raise ResourceWarning('医院不存在')
    else:
        hospital = Hospital()

    branches_encode(form)

    for k, v in form.data.items():
        setattr(hospital, k, v)

    hospital.updated_at = datetime.utcnow()
    hospital.save()

    return hospital


def form_init(form):
    region_ids = Region.objects(parent__exists=False).order_by('-id')
    choices = [('', '请选择一级区域')]
    if region_ids:
        for x in region_ids:
            choices.append((str(x.id), x.name))
        form.region_id.choices = choices

    region_id = form.data.get('region_id')
    choices = [('', '请先选择一级区域')]
    if region_id:
        region_ids = region_child_ids(region_id)
        if region_ids:
            for x in region_ids:
                choices.append((str(x.id), x.name))
    form.region_child_id.choices = choices



def region_child_ids(parent):
    if not parent:
        return None
    try:
        from bson import ObjectId
        parent = ObjectId(parent)
        return Region.objects(parent=parent).order_by('-id')
    except Exception as e:
        return None

@admin_hospital.route('/region_child', methods=['GET'])
@http_auth_required
def region_child():
    parent = request.args.get('parent')
    from flask import jsonify
    from bson import ObjectId
    choices = {}
    try:
        parent = ObjectId(parent)
        regions = Region.objects(parent=parent)
        if regions:
            for x in regions:
                choices.update({str(x.id): x.name})
    finally:
        # return json.dumps(choices)
        return jsonify(choices)


def branches_encode(form):
    branches = form.data.get('branches')
    if branches == '':
        form.branches.data = []
        return
    try:
        data_list = [x.strip(' ') for x in branches.split('\r\n')]
        data_text = '\n'.join(data_list)
        while '\n\n\n' in data_text:
            data_text = data_text.replace('\n\n\n', '\n\n')
        data_list = data_text.split('\n')
        branches = [data_list[i:i+3] for i in range(0, len(data_list), 3)]
        data = []
        for branch in branches:
            if len(branch) == 3:
                branch.pop()
            name, address = branch
            data.append({
                'name': name,
                'address': address,
            })
        form.branches.data = data
    except Exception as e:
        form.branches.errors.append('数据格式错误')
        raise ResourceWarning('数据格式错误')


def branches_decode(form):
    branches = form.data.get('branches')
    if branches and isinstance(branches, list):
        branches = '\r\n\r\n'.join(['{0}\r\n{1}'.format(branch.get('name'), branch.get('address')) for branch in branches])
        form.branches.data = branches
    else:
        form.branches.data = ''


# vim:ts=4:sw=4
