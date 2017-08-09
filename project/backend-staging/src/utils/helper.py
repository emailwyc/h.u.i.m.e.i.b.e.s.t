# vim: set fileencoding=utf-8 :

import hashlib
import uuid
import datetime
import time
import calendar
import random
import pytz
LOCAL_TIMEZONE = pytz.timezone("Asia/Shanghai")


def tomorrow():
    tomorrow = datetime.date.today() + datetime.timedelta(days=1)
    return datetime.datetime(tomorrow.year, tomorrow.month, tomorrow.day)


def utc(dt):
    if isinstance(dt, str) and len(dt) == 10:
        try:
            y, m, d = dt.split('-')
            dt = datetime.datetime(int(y), int(m), int(d))
        except:
            return dt
    if isinstance(dt, datetime.datetime):
        return LOCAL_TIMEZONE.localize(dt, is_dst=None).astimezone(pytz.utc)
    return dt


def insight(variable, run_functions=False, filters=set()):
    def pertty_print(x, z, width):
        print('{0:{fill}{align}{width}}: {1}'.format(x, z, fill=' ', align='<', width=width))
    _temp = []
    for x in dir(variable):
        if x.startswith('__') and x.endswith('__'):
            continue
        _temp.append(x)
    if filters:
        _temp = set(_temp) & filters
    width = len(max(_temp, key=len)) + 1
    print('\n{0} insight {1}\n'.format('>' * 3, '-' * 68))
    for x in _temp:
        z = getattr(variable, x)
        pertty_print(x, z, width)
        if callable(z) and run_functions:
            try:
                pertty_print('', z(), width)
            except Exception as e:
                pertty_print('', 'Exception: {0}'.format(e), width)
    print('\n{1} insight {0}\n'.format('<' * 3, '-' * 68))


def captcha(length=4):
    x = range(10)
    captcha = ''
    while length:
        captcha += str(random.choice(x))
        length -= 1
    return captcha


def salt(length=12):
    return uuid.uuid4().hex[:length]


def password_encrypt(plaintext, salt):
    return hashlib.sha1(plaintext.encode('utf-8') + salt.encode('utf-8')).hexdigest()


def password_verify(plaintext, salt, ciphertext):
    return password_encrypt(plaintext, salt) == ciphertext


def token():
    x = hashlib.sha1(salt(18).encode('utf-8')).hexdigest()
    y = hashlib.sha1(salt(24).encode('utf-8')).hexdigest()
    z = hashlib.sha1(salt(30).encode('utf-8')).hexdigest()
    return x[:15] + y[:15] + z[:12]


def request_sign(xtime, xkey):
    md5sum = hashlib.md5()
    md5sum.update(xtime.encode('utf-8') + xkey.encode('utf-8'))
    return md5sum.hexdigest()


def open_password(uid):
    salt = 'huimei'
    uid = str(uid)
    md5sum = hashlib.md5()
    md5sum.update(uid.encode('utf-8') + salt.encode('utf-8'))
    return md5sum.hexdigest()


def common_hashed(plaintext, salt):
    plaintext = str(plaintext)
    salt = str(salt)
    md5sum = hashlib.md5()
    md5sum.update(plaintext.encode('utf-8') + salt.encode('utf-8'))
    return md5sum.hexdigest()


def month_range(target_month):

    def month_format(year, month):
        month_obj = datetime.date(day=1, month=month, year=year)
        target_month = str(month_obj) + ' +0800'
        return datetime.datetime.strptime(target_month, '%Y-%m-%d %z')

    target_month = target_month + ' +0800'
    try:
        month_min = datetime.datetime.strptime(target_month, '%Y-%m %z')
    except ValueError:
        today = datetime.date.today()
        month_min = month_format(month=today.month, year=today.year)

    month = month_min.month + 1
    year = month_min.year
    if month > 12:
        month = 1
        year += 1
    month_max = month_format(month=month, year=year)
    month_str = month_min.strftime('%Y-%m')
    return month_str, month_min, month_max


def utc_datetime_filter(utc_datetime):
    if isinstance(utc_datetime, str):
        return utc_datetime
    if utc_datetime.utcoffset() is not None:
        utc_datetime = utc_datetime - utc_datetime.utcoffset()
    millis = int(calendar.timegm(utc_datetime.timetuple()))
    dt = datetime.datetime.fromtimestamp(time.mktime(time.localtime(millis)))
    return dt.strftime('%Y-%m-%d %H:%M:%S')

def utc_datetime_timestamp_filter(utc_datetime):
    ltime=time.localtime(utc_datetime)
    timeStr=time.strftime("%Y-%m-%d %H:%M:%S", ltime)
    return timeStr  


def utc_datetime_human_filter(utc_datetime):
    try:
        datetime_str = utc_datetime_filter(utc_datetime)
        utc_datetime = datetime.datetime.strptime(datetime_str, '%Y-%m-%d %H:%M:%S')
        isoweekday = utc_datetime.isoweekday()
        weeks = {
            1: '周一',
            2: '周二',
            3: '周三',
            4: '周四',
            5: '周五',
            6: '周六',
            7: '周日',
        }
        Ymd, HMS = datetime_str.split(' ')
        if '00:00:00' <= HMS < '12:00:00':
            human_str = '上午'
        elif '12:00:00' <= HMS < '18:00:00':
            human_str = '下午'
        elif '18:00:00' <= HMS < '24:00:00':
            human_str = '晚上'
        return '{0} {1} {2}'.format(Ymd[5:], weeks.get(isoweekday), human_str)
    except Exception as e:
        print(e)
        return datetime_str


if __name__ == '__main__':
    target_month = '2015-09'
    print(month_range(target_month))
    target_month = '2015-09 abc '
    print(month_range(target_month))


# vim:ts=4:sw=4
