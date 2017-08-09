# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Patient, PatientFamily, Order, Tag, Relation
from .tag import wrap_tags

rpc_patient = Blueprint('patient', __name__, url_prefix='/rpc/patient')


@rpc_patient.route('/info', methods=['GET', 'POST'])
@create_if_not_exist()
@auth_required('patient')
def info():
    if request.method == 'POST':
        patient = g.role_instance
        for k, v in g.form.items():
            setattr(patient, k, v)
        patient.updated_at = datetime.utcnow()
        patient.save()
        g.message = '设置成功'
    return display({'patient': basic_info(g.role_instance)})


def basic_info(patient):
    patient = Patient.objects(id=patient.id).first()
    if patient:
        return patient.to_bson()
    return None


@rpc_patient.route('/family', methods=['GET', 'POST'])
@auth_required('patient')
def family():
    if request.method == 'POST':
        family = None
        family_id = g.form.get('family_id', None)
        name = g.form.get('name')
        if family_id:
            if PatientFamily.objects(id__ne=family_id, patient=g.role_instance, name=name):
                return display(None, 409, '姓名重复')
            family = PatientFamily.objects(id=family_id, patient=g.role_instance).first()
        if not family:
            family = PatientFamily.objects(patient=g.role_instance, name=name).first()
        if not family:
            family = PatientFamily()
            family.patient = g.role_instance
        for k, v in g.form.items():
            setattr(family, k, v)
        family.updated_at = datetime.utcnow()
        family.save()
        g.message = '设置成功'
    return display({'family': family_show(g.role_instance)})


def family_show(patient):
    family = PatientFamily.objects(patient=patient)
    if family:
        return [x.to_bson() for x in family]
    return None


@rpc_patient.route('/comment', methods=['POST'])
@auth_required('doctor')
def comment():
    patient_id = g.form.get('patient_id')
    g.message = '患者不存在'
    patient = Patient.objects(id=patient_id).first_or_404()
    comment = g.form.get('comment')
    doctor = g.role_instance
    relation = Relation.objects(doctor=doctor, patient=patient).first()
    if not relation:
        relation = Relation()
        relation.doctor = doctor
        relation.patient = patient
    relation.comment = comment
    relation.save()
    g.message = '设置成功'
    return display(None)


@rpc_patient.route('/bulk_info', methods=['POST'])
@auth_required('doctor', 'patient')
def bulk_info():

    def locate_tags(patient):
        tags = Tag.objects(doctor=doctor, patients=patient)
        return wrap_tags(tags)

    def converter(patient):
        if not isinstance(patient, Patient):
            return None
        relation = Relation.objects(doctor=doctor, patient=patient).first()
        comment = relation.comment if relation else ''
        x = patient.to_bson()
        x.update({'mobile': patient.uref.mobile})
        x.update({'tags': locate_tags(patient)})
        x.update({'comment': comment})
        return x

    doctor = g.role_instance
    # patient id
    ids = g.form.get('ids')
    documents = Patient.objects().in_bulk(object_ids=ids)
    patients = []
    if documents:
        for k, v in documents.items():
            patient = converter(v)
            if patient:
                patients.append(patient)
    return display({'patients': patients})

    '''
    # order id
    ids = g.form.get('ids')
    documents = Order.objects().in_bulk(object_ids=ids)
    patients = []
    if documents:
        def converter(patient):
            if not isinstance(patient, Patient):
                return None
            x = patient.to_bson()
            x.update({'mobile': patient.uref.mobile})
            return x
        for k, v in documents.items():
            patient = converter(v)
            if patient:
                patients.append(patient)
    return display({'patients': patients})
    '''

    '''
    # patient id and doctor id
    ids = g.form.get('ids')
    patient_objs = Patient.objects().in_bulk(object_ids=ids)
    doctor_objs = Doctor.objects().only('id', 'uref', 'name', 'avatar').in_bulk(object_ids=ids)
    patient_objs.update(doctor_objs)
    patients = []
    if patient_objs:
        def converter(v):
            x = v.to_bson()
            x.update({'mobile': v.uref.mobile})
            x.update({'age': 0 if 'age' not in v else v.age})
            x.update({'gender': '' if 'gender' not in v else v.gender})
            return x
        patients = [converter(v) for k, v in patient_objs.items()]
        for i in patients:
            if 'service_provided' in i:
                i.pop('service_provided')
            if 'service_settings' in i:
                i.pop('service_settings')
            if 'starred' in i:
                i.pop('starred')
    return display({'patients': patients})
    '''


# vim:ts=4:sw=4
