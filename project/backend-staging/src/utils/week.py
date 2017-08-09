# vim: set fileencoding=utf-8 :

import datetime
import isoweek


class Week(object):

    def __init__(self, limited=3):
        self.year, self.weeknum, self.weekday = datetime.date.today().isocalendar()
        self.limited = limited

    def limit(self, limited=3):
        self.limited = limited
        return self

    def dates(self):
        self.weeks = {0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: []}
        for delta in range(self.limited):
            self._fill(self.weeks, delta)
        self.weeks.update({7: self.weeks.pop(0)})
        return self.weeks

    def bounds(self):
        starts = self._calc(0, 1)
        ends = self._calc(self.limited, 1)
        return starts, ends

    def _calc(self, delta, weekday):
        yw_str = str(isoweek.Week(self.year, self.weeknum+delta))
        year_with_week = '{YW} w:{w}'.format(YW=yw_str, w=weekday)
        return datetime.datetime.strptime(year_with_week, '%YW%W w:%w').strftime('%Y-%m-%d')

    def _fill(self, week, delta):
        weekday = 0
        while weekday < 7:
            week[weekday].append(self._calc(delta, weekday))
            weekday += 1


if __name__ == '__main__':
    print(Week().limit(3).dates())
    print(Week().limit(3).bounds())


# vim:ts=4:sw=4
