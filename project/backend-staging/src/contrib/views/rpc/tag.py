# vim: set fileencoding=utf-8 :

from .. import *
from ...models import User, Doctor, Patient, Tag, Order, Relation

rpc_tag = Blueprint('tag', __name__, url_prefix='/rpc/tag')


@rpc_tag.route('/list', methods=['GET'])
@auth_required('doctor')
def tags_list():
    doctor = g.role_instance
    tags = Tag.objects(doctor=doctor).order_by('+id')
    if not tags:
        reset_tag(doctor)
        tags = Tag.objects(doctor=doctor)
    return display({'tags': wrap_tags(tags), 'summary': summary(doctor)})


def summary(doctor):
    tags = Tag.objects(doctor=doctor)
    if not tags:
        reset_tag(doctor)
    patients = 0
    for tag in tags:
        patients += len(tag.patients)

    data = {
        'patients': patients,
        'starred': doctor.starred,
    }
    return data


@rpc_tag.route('/create', methods=['POST'])
@auth_required('doctor')
def create():
    doctor = g.role_instance
    name = g.form.get('name')
    if Tag.objects(doctor=doctor, name=name):
        g.message = '已经存在'
        abort(400)
    tag = Tag()
    tag.scope = 'user'
    tag.doctor = doctor
    tag.name = name
    tag.save()
    tag.reload()
    g.message = '创建成功'
    return display({'tag': wrap_tag(tag)})


@rpc_tag.route('/update', methods=['POST'])
@auth_required('doctor')
def update():
    doctor = g.role_instance
    id = g.form.get('id')
    name = g.form.get('name')
    if Tag.objects(doctor=doctor, id__ne=id, name=name):
        g.message = '已经存在'
        abort(400)
    g.message = '不存在'
    tag = Tag.objects(doctor=doctor, id=id).first_or_404()
    if tag.scope != 'user':
        g.message = '更新失败'
        abort(400)
    tag.name = name
    tag.updated_at = datetime.utcnow()
    tag.save()
    tag.reload()
    g.message = '更新成功'
    return display({'tag': wrap_tag(tag)})


@rpc_tag.route('/delete', methods=['POST'])
@auth_required('doctor')
def delete():
    doctor = g.role_instance
    id = g.form.get('id')
    g.message = '不存在'
    tag = Tag.objects(doctor=doctor, id=id).first_or_404()
    if tag.scope != 'user':
        g.message = '无法删除'
        abort(400)
    if len(tag.patients) > 0:
        default_tag = Tag.objects(doctor=doctor, scope='default').first()
        default_tag.patients = list(set(default_tag.patients + tag.patients))
        default_tag.save()
    tag.delete()
    g.message = '删除成功'
    return display(None)


@rpc_tag.route('/bulk_info', methods=['POST'])
@auth_required('doctor')
def bulk_info():
    doctor = g.role_instance
    ids = g.form.get('ids')
    return tags_info(ids)


def tags_info(ids):
    data = {}
    doctor = g.role_instance
    tags = Tag.objects(doctor=doctor).in_bulk(object_ids=ids)
    if tags:

        def locate_tags(patient):
            tags = Tag.objects(doctor=doctor, patients=patient)
            return wrap_tags(tags)

        def patient_extend(patient):
            if not isinstance(patient, Patient):
                return None
            relation = Relation.objects(doctor=doctor, patient=patient).first()
            comment = relation.comment if relation else ''
            x = patient.to_bson()
            x.update({'mobile': patient.uref.mobile})
            x.update({'tags': locate_tags(patient)})
            x.update({'comment': comment})
            return x

        def converter(tag):
            patients = []
            for i in tag.patients:
                patient = patient_extend(i)
                if patient:
                    patients.append(patient)
            _tag = tag.to_bson()
            _tag.update({'patients': patients})
            _tag.update({'count': len(tag.patients)})
            return _tag

        for k, v in tags.items():
            data.update({str(k): converter(v)})
    return display({'tags_info': data})


@rpc_tag.route('/transfer', methods=['POST'])
@auth_required('doctor')
def transfer():
    doctor = g.role_instance
    id = g.form.get('id')
    transfer_id = g.form.get('transfer_id')
    patient_id = g.form.get('patient_id')
    g.message = '不存在'
    tag = Tag.objects(doctor=doctor, id=id).first_or_404()
    transfer_tag = Tag.objects(doctor=doctor, id=transfer_id).first_or_404()
    g.message = '移动失败'
    scope_allow = ('default', 'user')
    if tag.scope not in scope_allow or transfer_tag.scope not in scope_allow:
        abort(400)
    patient = Patient.objects(id=patient_id).first_or_404()
    if Tag.objects(doctor=doctor, id=transfer_id).update_one(add_to_set__patients=patient):
        if Tag.objects(doctor=doctor, id=id).update_one(pull__patients=patient):
            g.message = '移动成功'
            return display({'tag': wrap_tag(transfer_tag)})
    abort(400)


@rpc_tag.route('/reset', methods=['POST'])
@auth_required('doctor')
def reset():
    doctor = g.role_instance
    reset_tag(doctor)
    g.message = '重置成功'
    return tags_list()


@rpc_tag.route('/locate', methods=['POST'])
@auth_required('doctor')
def locate():
    doctor = g.role_instance
    patient_id = g.form.get('patient_id')
    patient = Patient.objects(id=patient_id).first_or_404()
    tags = Tag.objects(doctor=doctor, patients=patient)
    tags_located = wrap_tags(tags)
    return display({'tags_located': tags_located})


def wrap_tag(tag):
    if not tag:
        return None
    _tag = tag.to_bson(exclude='patients')
    _tag.update({'count': len(tag.patients)})
    return _tag


def wrap_tags(tags):
    data = []
    if not tags:
        return data
    for item in tags:
        _item = item.to_bson(exclude='patients')
        _item.update({'count': len(item.patients)})
        data.append(_item)
    return data


def reset_tag(doctor):
    Tag.objects(doctor=doctor).delete()
    tag = Tag()
    tag.scope = 'default'
    tag.doctor = doctor
    tag.name = '我的患者'
    orders = Order.objects(doctor=doctor)
    if orders:
        tag.patients = list(set([item.patient for item in orders]))
    tag.save()


# vim:ts=4:sw=4
