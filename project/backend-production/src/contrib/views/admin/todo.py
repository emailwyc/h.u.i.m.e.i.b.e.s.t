# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorAssistant, Order
from flask import render_template, redirect, url_for, flash
from .order import query_orders
import datetime


admin_todo = Blueprint('admin_todo', __name__, url_prefix='/admin/todo')


def utc(dt):
    return LOCAL.localize(dt, is_dst=None).astimezone(pytz.utc)


@admin_todo.route('/order', methods=['GET'])
@http_auth_required
def todo_order():
    starts = helper.utc(helper.tomorrow())
    ends = starts + datetime.timedelta(days=1)
    orders = query_orders(starts, ends)
    return render_template('admin/order/list.html', orders=orders)


# vim:ts=4:sw=4
