# vim: set fileencoding=utf-8 :

from .. import *
from ...models import User, Doctor, Template

rpc_template = Blueprint('template', __name__, url_prefix='/rpc/template')


@rpc_template.route('/list', methods=['GET'])
@auth_required('doctor')
def template_list():
    doctor = g.role_instance
    templates = Template.objects(doctor=doctor).order_by('-id')
    return display({'templates': (t.to_bson() for t in templates)})


@rpc_template.route('/create', methods=['POST'])
@auth_required('doctor')
def create():
    doctor = g.role_instance
    template = Template()
    template.doctor = doctor
    for k, v in g.form.items():
        setattr(template, k, v)
    template.save()
    template.reload()
    g.message = '创建成功'
    return display({'template': template.to_bson()})


@rpc_template.route('/update', methods=['POST'])
@auth_required('doctor')
def update():
    doctor = g.role_instance
    id = g.form.get('id')
    g.message = '不存在'
    template = Template.objects(doctor=doctor, id=id).first_or_404()
    for k, v in g.form.items():
        setattr(template, k, v)
    template.updated_at = datetime.utcnow()
    template.save()
    template.reload()
    g.message = '更新成功'
    return display({'template': template.to_bson()})


@rpc_template.route('/delete', methods=['POST'])
@auth_required('doctor')
def delete():
    doctor = g.role_instance
    id = g.form.get('id')
    g.message = '不存在'
    template = Template.objects(doctor=doctor, id=id).first_or_404()
    template.delete()
    g.message = '删除成功'
    return display(None)


# vim:ts=4:sw=4
