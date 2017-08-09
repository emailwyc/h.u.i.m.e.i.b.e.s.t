# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorTimetable, Order
from flask import render_template, redirect, url_for, flash
from utils.week import Week
from copy import deepcopy
from utils import render


admin_analytics = Blueprint('admin_analytics', __name__, url_prefix='/admin/analytics')


@admin_analytics.route('/doctor', methods=['GET'])
@http_auth_required
def order_list():
    data = None
    month_str = request.args.get('when', '2016-01')
    if month_str and len(month_str) == 7:
        data = items(month_str)
    return render_template('admin/analytics/doctor.html', data=data, month_str=month_str)


def items(month_str):
    month_str, start_time, end_time = helper.month_range(month_str)
    start_time_str = start_time.strftime('%Y-%m-%d')
    end_time_str = end_time.strftime('%Y-%m-%d')

    criteria_order = {
        'schedule__gte': start_time,
        'schedule__lte': end_time,
        'status__in': ['已就诊', '已完成'],
    }
    criteria_timetable = {
        'date__gte': start_time_str,
        'date__lte': end_time_str,
    }

    _doctor_pool = []
    orders = Order.objects(**criteria_order)
    data = []
    if orders:
        for order in orders:
            if order.doctor in _doctor_pool:
                continue
            _doctor_pool.append(order.doctor)
            detail, revenue_total = doctor_detail(order.doctor, deepcopy(criteria_order), deepcopy(criteria_timetable))
            item = {
                'doctor': {'id': order.doctor.id, 'name': order.doctor.name, 'hospital': order.doctor.hospital},
                'detail': detail,
                'revenue_total': revenue_total,
            }
            data.append(item)

    doctors = Doctor.objects(freeze='no').order_by('+created_at')
    for doctor in doctors:
        if doctor in _doctor_pool:
            continue
        _doctor_pool.append(doctor)
        detail, revenue_total = doctor_detail(doctor, deepcopy(criteria_order), deepcopy(criteria_timetable))
        item = {
            'doctor': {'id': doctor.id, 'name': doctor.name, 'hospital': doctor.hospital},
            'detail': detail,
            'revenue_total': '-',
        }
        data.append(item)

    return data


def doctor_detail(doctor, criteria_order, criteria_timetable):
    criteria_order.update({'doctor': doctor})
    criteria_timetable.update({'doctor': doctor})
    detail = {}

    revenue_total = 0
    for service in ('clinic', 'consult', 'phonecall'):
        criteria_order.update({'service': service})
        orders = Order.objects(**criteria_order)
        revenue = 0
        count = 0
        price = []
        for order in orders:
            revenue += order.price
            price.append(str(order.price))
            if service == 'phonecall':
                count += order.call_minutes
            else:
                count += 1
        revenue_total += revenue
        price = set(price)
        if not price:
            price = ['-']

        criteria_timetable.update({'service': service})
        quantity = 0
        current_price = []
        timetables = DoctorTimetable.objects(**criteria_timetable)
        if timetables:
            for timetable in timetables:
                if service == 'phonecall':
                    quantity += timetable.minutes_quantity
                else:
                    quantity += timetable.quantity
                    current_price.append(str(timetable.price))

        def _handle_price(price):
            if price == -1:
                return '关闭'
            elif price == 0:
                return '义诊'
            else:
                return str(int(price)) if price > 1 else str(price)

        try:
            _service = doctor.service_provided.get(service)
            on = _service.on
            if service == 'phonecall':
                current_price = [
                        '05分钟: {0}'.format(_handle_price(_service.price_05)),
                        '10分钟: {0}'.format(_handle_price(_service.price_10)),
                        '15分钟: {0}'.format(_handle_price(_service.price_15)),
                        '20分钟: {0}'.format(_handle_price(_service.price_20)),
                        ]
            elif service == 'consult':
                current_price = [_service.price]
        except Exception as e:
            on = False
            current_price = ['-']
        if service != 'phonecall':
            current_price = set(current_price)
            if not current_price:
                current_price = ['-']

            
        detail.update({service: {
            'price': price,
            'quantity': quantity,
            'count': count,
            'current_price': current_price,
            'on': on,
        }})

    return detail, revenue_total

# vim:ts=4:sw=4
