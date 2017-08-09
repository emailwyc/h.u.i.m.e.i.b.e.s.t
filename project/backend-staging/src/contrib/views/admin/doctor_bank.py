# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorPrivate, DoctorAssistant, Order
from ...forms.admin.doctor_bank import QueryForm
from flask import render_template, redirect, url_for, flash


admin_doctor_bank = Blueprint('admin_doctor_bank', __name__, url_prefix='/admin/doctor_bank')

_SERVICE_TYPE_ = dict(SERVICE_TYPE)

@admin_doctor_bank.route('/query', methods=['GET', 'POST'])
@http_auth_required
def order_query():
    order = False
    doctor_private = False
    form = QueryForm()

    if request.method == 'POST' and form.validate_on_submit():
        order_id = form.data.get('order_id')
        order = Order.objects(id=order_id).first()
        if order:
            if order.service in ('clinic', 'phonecall'):
                if order.status == '已支付':
                    order.status = '待就诊'
                if order.status == '已完成':
                    order.status = '已就诊'
            if order.service in ('consult'):
                if order.status == '已支付':
                    order.status = '咨询中'
                if order.status == '已完成':
                    order.status = '已结束'

            order.service = _SERVICE_TYPE_.get(order.service)

            if order.doctor.assistant:
                assistant = DoctorAssistant.objects(id=order.doctor.assistant).first()
                if assistant:
                    order.doctor.assistant = assistant.name
                doctor_private = DoctorPrivate.objects(id=order.doctor.id).first()
                if doctor_private:
                    doctor_private.doctor_name = order.doctor.name

    return render_template('admin/doctor_bank/detail.html', form=form, order=order, doctor_private=doctor_private)


# vim:ts=4:sw=4
