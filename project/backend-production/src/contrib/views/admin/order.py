# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorAssistant, Order, OrderQrcode
from flask import render_template, redirect, url_for, flash
from utils.week import Week


admin_order = Blueprint('admin_order', __name__, url_prefix='/admin/order')


@admin_order.route('/list', methods=['GET'])
@http_auth_required
def order_list():
    starts, ends = Week().limit(3).bounds()
    starts = helper.utc(starts)
    ends = helper.utc(ends)
    orders = query_orders(starts, ends)
    return render_template('admin/order/list.html', orders=orders)


def query_orders(starts, ends):
    _status = ['已支付', '待就诊', '已就诊', '已完成', '已取消']
    orders = Order.objects(service='clinic', schedule__gte=starts, schedule__lt=ends, status__in=_status).order_by('+schedule', '+created_at')

    for item in orders:
        if item.status == '已支付':
            item.status = '待就诊'
        if item.status == '已完成':
            item.status = '已就诊'
        if item.doctor.assistant:
            assistant = DoctorAssistant.objects(id=item.doctor.assistant).first()
            if assistant:
                item.doctor.assistant = assistant.name

    return orders

@admin_order.route('/qrcode', methods=['GET'])
@http_auth_required
def order_qrcode():
    try:
        orderInfo= OrderQrcode.objects(status=1).order_by('-id').skip(0 * 100).limit(100)
        for item in orderInfo:
            if item.status == 0:
                item.status = '新订单'
            if item.status == 1:
                item.status = '已支付'
            if item.pay_at:
                item.pay_at= helper.utc_datetime_timestamp_filter(item.pay_at)
    except Exception as e:
        print(e)
    return render_template('admin/order/qrcode.html',orders=orderInfo)




# vim:ts=4:sw=4
