# vim: set fileencoding=utf-8 :

import base64
import calendar
import collections
import datetime
import json
import re
import uuid

from decimal import Decimal

from bson import EPOCH_AWARE, RE_TYPE, SON
from bson.binary import Binary
from bson.code import Code
from bson.dbref import DBRef
from bson.int64 import Int64
from bson.max_key import MaxKey
from bson.min_key import MinKey
from bson.objectid import ObjectId
from bson.regex import Regex
from bson.timestamp import Timestamp
from bson.tz_util import utc

from bson.py3compat import PY3, iteritems, string_type, text_type


def jsonify(data, status=0, message=None):
    return dumps({'data': data, 'status': status, 'message': message})


def dumps(obj, *args, **kwargs):
    """Helper function that wraps :class:`json.dumps`.

    Recursive function that handles all BSON types including
    :class:`~bson.binary.Binary` and :class:`~bson.code.Code`.

    .. versionchanged:: 2.7
       Preserves order when rendering SON, Timestamp, Code, Binary, and DBRef
       instances.
    """
    kwargs.update({'ensure_ascii': False, 'indent': None, 'sort_keys': True})
    return json.dumps(_json_convert(obj), *args, **kwargs)


def _json_convert(obj):
    """Recursive helper method that converts BSON types so they can be
    converted into json.
    """
    if hasattr(obj, 'iteritems') or hasattr(obj, 'items'):  # PY3 support
        return SON(((k, _json_convert(v)) for k, v in iteritems(obj) if k not in ('_cls', '_id')))
    elif hasattr(obj, '__iter__') and not isinstance(obj, (text_type, bytes)):
        return list((_json_convert(v) for v in obj))
    try:
        return default(obj)
    except TypeError:
        return obj


def default(obj):
    # We preserve key order when rendering SON, DBRef, etc. as JSON by
    # returning a SON for those types instead of a dict.
    if isinstance(obj, ObjectId):
        return str(obj)
    if isinstance(obj, DBRef):
        return _json_convert(obj.as_doc())
    if isinstance(obj, Decimal):
        return obj.to_eng_string()
    if isinstance(obj, datetime.datetime):
        # TODO share this code w/ bson.py?
        if obj.utcoffset() is not None:
            obj = obj - obj.utcoffset()
        # datetime.utcfromtimestamp(timestamp)
        # millis = int(calendar.timegm(obj.timetuple()) * 1000 + obj.microsecond / 1000)
        millis = int(calendar.timegm(obj.timetuple()))
        return millis
    if isinstance(obj, (RE_TYPE, Regex)):
        flags = ""
        if obj.flags & re.IGNORECASE:
            flags += "i"
        if obj.flags & re.LOCALE:
            flags += "l"
        if obj.flags & re.MULTILINE:
            flags += "m"
        if obj.flags & re.DOTALL:
            flags += "s"
        if obj.flags & re.UNICODE:
            flags += "u"
        if obj.flags & re.VERBOSE:
            flags += "x"
        if isinstance(obj.pattern, text_type):
            pattern = obj.pattern
        else:
            pattern = obj.pattern.decode('utf-8')
        return SON([("$regex", pattern), ("$options", flags)])
    if isinstance(obj, MinKey):
        return {"$minKey": 1}
    if isinstance(obj, MaxKey):
        return {"$maxKey": 1}
    if isinstance(obj, Timestamp):
        return {"$timestamp": SON([("t", obj.time), ("i", obj.inc)])}
    if isinstance(obj, Code):
        return SON([('$code', str(obj)), ('$scope', obj.scope)])
    if isinstance(obj, Binary):
        return SON([
            ('$binary', base64.b64encode(obj).decode()),
            ('$type', "%02x" % obj.subtype)])
    if PY3 and isinstance(obj, bytes):
        return SON([
            ('$binary', base64.b64encode(obj).decode()),
            ('$type', "00")])
    if isinstance(obj, uuid.UUID):
        return {"$uuid": obj.hex}
    raise TypeError("%r is not JSON serializable" % obj)

# vim:ts=4:sw=4
