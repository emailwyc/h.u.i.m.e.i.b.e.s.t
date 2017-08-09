# vim: set fileencoding=utf-8 :

from .. import *
from ...models import Doctor, DoctorTimetable
from flask import render_template, redirect, url_for, flash
import json
from bson import ObjectId


admin_cron = Blueprint('admin_cron', __name__, url_prefix='/admin/cron')


@admin_cron.route('/rolling_over', methods=['GET'])
@http_auth_required
def rolling_over():
    '''
    a_starts  = '2015-11-09'
    a_ends    = '2015-11-15'
    b_starts = '2015-11-16'
    b_ends   = '2015-11-22'
    rolling_over_rules = {
        '2015-11-09': '2015-11-16',
        '2015-11-10': '2015-11-17',
        '2015-11-11': '2015-11-18',
        '2015-11-12': '2015-11-19',
        '2015-11-13': '2015-11-20',
        '2015-11-14': '2015-11-21',
        '2015-11-15': '2015-11-22',
    }
    a_starts  = '2015-11-16'
    a_ends    = '2015-11-22'
    b_starts = '2015-11-23'
    b_ends   = '2015-11-29'
    rolling_over_rules = {
        '2015-11-16': '2015-11-23',
        '2015-11-17': '2015-11-24',
        '2015-11-18': '2015-11-25',
        '2015-11-19': '2015-11-26',
        '2015-11-20': '2015-11-27',
        '2015-11-21': '2015-11-28',
        '2015-11-22': '2015-11-29',
    }
    '''
    a_starts  = '2015-11-23'
    a_ends    = '2015-11-29'
    b_starts = '2015-11-30'
    b_ends   = '2015-12-06'
    rolling_over_rules = {
        '2015-11-23': '2015-11-30',
        '2015-11-24': '2015-12-01',
        '2015-11-25': '2015-12-02',
        '2015-11-26': '2015-12-03',
        '2015-11-27': '2015-12-04',
        '2015-11-28': '2015-12-05',
        '2015-11-29': '2015-12-06',
    }
    dry_run = True
    # dry_run = False
    timetables = DoctorTimetable.objects(date__gte=a_starts, date__lte=a_ends).order_by('+id')
    data = []
    if timetables:
        for item in timetables:
            doctor = item.doctor
            date = rolling_over_rules.get(item.date)
            interval = item.interval
            _timetables = DoctorTimetable.objects(doctor=doctor, date=date, interval=interval)
            if _timetables:
                continue
            tt = DoctorTimetable()
            for key in ('doctor', 'service', 'interval', 'weekday', 'repeat', 'location',
                      'alert', 'action'):
                setattr(tt, key, getattr(item, key))
            tt.date = date
            tt.quantity = 0
            if not dry_run:
                tt.save()
            log = {
                'from': {'id': item.id, 'doctor': item.doctor.id, 'date': item.date, 'interval': item.interval},
                'to': {'id': tt.id, 'doctor': tt.doctor.id, 'date': tt.date, 'interval': tt.interval}
            }
            data.append(log)
            tt = None
    return display({'data': data, 'count': len(data)})


@admin_cron.route('/recovery_from_log', methods=['GET'])
@http_auth_required
def recovery_from_log():
    log_file = 'tools/scripts/cron/production-2015-11-23.log'
    log = None
    with open(log_file) as f:
        x = json.load(f)
        log = x.get('data')

    g.message = 'log data error'
    if not isinstance(log, dict):
        abort(400)
    count_a = log.get('count')
    timetables = log.get('data')
    count_b = len(timetables)
    if count_a != count_b:
        abort(400)

    timetables = timetables[0:1]
    for tt in timetables:
        tt_a = tt.get('from')
        tt_b = tt.get('to')
        doctor = Doctor.objects(id=tt_a.get('doctor')).first()
        if doctor:
            print(doctor.locations[0])

    data = ''
    g.message = 'log data loaded'
    return display(data)


# vim:ts=4:sw=4
